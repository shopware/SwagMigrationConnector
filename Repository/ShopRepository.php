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

class ShopRepository extends AbstractRepository
{
    /**
     * {@inheritdoc}
     */
    public function requiredForCount(array $entities)
    {
        return !\in_array(DefaultEntities::SALES_CHANNEL, $entities);
    }

    /**
     * {@inheritdoc}
     */
    public function getTotal()
    {
        $total = (int) $this->connection->createQueryBuilder()
            ->select('COUNT(*)')
            ->from('s_core_shops')
            ->execute()
            ->fetchColumn();

        return new TotalStruct(DefaultEntities::SALES_CHANNEL, $total);
    }

    public function fetch($offset = 0, $limit = 250)
    {
        $ids = $this->fetchIdentifiers('s_core_shops', $offset, $limit);

        $query = $this->connection->createQueryBuilder();

        $query->from('s_core_shops', 'shop');
        $query->addSelect('shop.id as identifier');
        $this->addTableSelection($query, 's_core_shops', 'shop');

        $query->leftJoin('shop', 's_core_locales', 'locale', 'shop.locale_id = locale.id');
        $query->addSelect('locale.locale');

        $query->leftJoin('shop', 's_core_currencies', 'currency', 'shop.currency_id = currency.id');
        $query->addSelect('currency.currency');

        $query->where('shop.id IN (:ids)');

        $query->setParameter('ids', $ids, Connection::PARAM_STR_ARRAY);
        $query->orderBy('shop.main_id');

        return $query->execute()->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_UNIQUE);
    }
}
