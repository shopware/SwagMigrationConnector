<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Repository;

use SwagMigrationConnector\Util\DefaultEntities;
use SwagMigrationConnector\Util\TotalStruct;

class ProductPropertyRelationRepository extends AbstractRepository
{
    public function requiredForCount(array $entities)
    {
        return !\in_array(DefaultEntities::PRODUCT_PROPERTY_RELATION, $entities);
    }

    public function getTotal()
    {
        $total = (int) $this->connection->createQueryBuilder()
            ->select('COUNT(*)')
            ->from('s_filter_values', 'filter_value')
            ->innerJoin('filter_value', 's_filter_articles', 'filter_products', 'filter_value.id = filter_products.valueID')
            ->execute()
            ->fetchColumn();

        return new TotalStruct(DefaultEntities::PRODUCT_PROPERTY_RELATION, $total);
    }

    public function fetch($offset = 0, $limit = 250)
    {
        $query = $this->connection->createQueryBuilder();

        $query->from('s_filter_values', 'filter_value');
        $query->addSelect('"property" AS type');
        $query->addSelect('value AS name');
        $this->addTableSelection($query, 's_filter_values', 'filter_value');

        $query->innerJoin('filter_value', 's_filter_articles', 'filter_product', 'filter_value.id = filter_product.valueID');
        $query->addSelect('MD5(CONCAT(filter_value.id, filter_product.articleID)) AS identifier');
        $query->addSelect('filter_product.articleID AS productId');

        $query->leftJoin('filter_value', 's_filter_options', 'filter_value_group', 'filter_value_group.id = filter_value.optionID');
        $this->addTableSelection($query, 's_filter_options', 'filter_value_group');

        $query->setFirstResult($offset);
        $query->setMaxResults($limit);

        return $query->execute()->fetchAll();
    }
}
