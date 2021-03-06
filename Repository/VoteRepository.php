<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Repository;

use Doctrine\DBAL\Connection;
use SwagMigrationConnector\Util\DefaultEntities;
use SwagMigrationConnector\Util\TotalStruct;

class VoteRepository extends AbstractRepository
{
    /**
     * {@inheritdoc}
     */
    public function requiredForCount(array $entities)
    {
        return !\in_array(DefaultEntities::PRODUCT_REVIEW, $entities);
    }

    /**
     * {@inheritdoc}
     */
    public function getTotal()
    {
        $total = (int) $this->connection->createQueryBuilder()
            ->select('COUNT(*)')
            ->from('s_articles_vote')
            ->execute()
            ->fetchColumn();

        return new TotalStruct(DefaultEntities::PRODUCT_REVIEW, $total);
    }

    /**
     * {@inheritdoc}
     */
    public function fetch($offset = 0, $limit = 250)
    {
        $ids = $this->fetchIdentifiers('s_articles_vote', $offset, $limit);

        $query = $this->connection->createQueryBuilder();

        $query->from('s_articles_vote', 'vote');
        $this->addTableSelection($query, 's_articles_vote', 'vote');

        $query->leftJoin('vote', 's_core_shops', 'shop', 'shop.id = vote.shop_id OR (vote.shop_id IS NULL AND shop.default = 1)');
        $query->addSelect('shop.id as mainShopId');
        $query->leftJoin('shop', 's_core_locales', 'locale', 'shop.locale_id = locale.id');
        $query->addSelect('locale.locale as _locale');

        $query->where('vote.id IN (:ids)');
        $query->setParameter('ids', $ids, Connection::PARAM_STR_ARRAY);

        $query->addOrderBy('vote.id');

        return $query->execute()->fetchAll();
    }
}
