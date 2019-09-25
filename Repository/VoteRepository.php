<?php declare(strict_types=1);

namespace SwagMigrationConnector\Repository;

use Doctrine\DBAL\Connection;

class VoteRepository extends AbstractRepository
{
    /**
     * {@inheritdoc}
     */
    public function fetch($offset = 0, $limit = 250)
    {
        $ids = $this->fetchIdentifiers('s_articles_vote', $offset, $limit);

        $query = $this->connection->createQueryBuilder();

        $query->from('s_articles_vote', 'vote');
        $this->addTableSelection($query, 's_articles_vote', 'vote');

        $query->leftJoin('vote', 's_core_shops', 'shop', 'shop.id = vote.shop_id');
        $query->leftJoin('shop', 's_core_locales', 'locale', 'shop.locale_id = locale.id');
        $query->addSelect('locale.locale as _locale');

        $query->where('vote.id IN (:ids)');
        $query->setParameter('ids', $ids, Connection::PARAM_STR_ARRAY);

        $query->addOrderBy('vote.id');

        return $query->execute()->fetchAll();
    }
}