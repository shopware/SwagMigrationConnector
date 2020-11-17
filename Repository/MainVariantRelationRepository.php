<?php declare(strict_types=1);
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Repository;

use SwagMigrationConnector\Util\DefaultEntities;
use SwagMigrationConnector\Util\TotalStruct;

class MainVariantRelationRepository extends AbstractRepository
{
    public function requiredForCount(array $entities)
    {
        return !\in_array(DefaultEntities::MAIN_VARIANT_RELATION, $entities);
    }

    public function getTotal()
    {
        $total = (int) $this->connection->createQueryBuilder()
            ->select('COUNT(*)')
            ->from('s_articles')
            ->where('main_detail_id IS NOT NULL')
            ->andWhere('configurator_set_id IS NOT NULL')
            ->execute()
            ->fetchColumn();

        return new TotalStruct(DefaultEntities::MAIN_VARIANT_RELATION, $total);
    }

    public function fetch($offset = 0, $limit = 250)
    {
        $query = $this->connection->createQueryBuilder()
            ->addSelect('articles.id, details.ordernumber')
            ->from('s_articles', 'articles')
            ->innerJoin('articles', 's_articles_details', 'details', 'details.id = articles.main_detail_id')
            ->where('main_detail_id IS NOT NULL')
            ->andWhere('configurator_set_id IS NOT NULL')
            ->orderBy('articles.id')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->execute();

        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }
}
