<?php

namespace SwagMigrationApi\Repository;

use Doctrine\DBAL\Connection;

class ProductRepository extends AbstractRepository
{
    /**
     * @param int $offset
     * @param int $limit
     *
     * @return array
     */
    public function getProducts($offset = 0, $limit = 250)
    {
        $query = $this->getConnection()->createQueryBuilder();

        $query->from('s_articles_details', 'product_detail');
        $this->addTableSelection($query, 's_articles_details', 'product_detail');

        $query->leftJoin('product_detail', 's_articles', 'product', 'product.id = product_detail.articleID');
        $this->addTableSelection($query, 's_articles', 'product');

        $query->leftJoin('product', 's_core_tax', 'tax', 'product.taxID = tax.id');
        $this->addTableSelection($query, 's_core_tax', 'tax');

        $query->leftJoin('product', 's_articles_attributes', 'attributes', 'product_detail.id = attributes.articledetailsID');
        $this->addTableSelection($query, 's_articles_attributes', 'attributes');

        $query->leftJoin('product', 's_articles_supplier', 'supplier', 'product.supplierID = supplier.id');
        $this->addTableSelection($query, 's_articles_supplier', 'supplier');

        $query->setFirstResult($offset);
        $query->setMaxResults($limit);

        return $query->execute()->fetchAll();
    }

    /**
     * @param array $ids
     *
     * @return array
     */
    public function fetchProductPrices(array $ids)
    {
        $query = $this->getConnection()->createQueryBuilder();

        $query->from('s_articles_prices', 'price');
        $query->addSelect('price.articledetailsID');
        $this->addTableSelection($query, 's_articles_prices', 'price');

        $query->leftJoin('price', 's_core_customergroups', 'customer_group', 'price.pricegroup = customer_group.groupkey');
        $this->addTableSelection($query, 's_core_customergroups', 'customer_group');

        $query->leftJoin('price', 's_articles_prices_attributes', 'attribute', 'price.id = attribute.id');
        $this->addTableSelection($query, 's_articles_prices_attributes', 'attribute');

        $query->where('price.articledetailsID IN (:ids)');
        $query->setParameter('ids', $ids, Connection::PARAM_INT_ARRAY);

        return $query->execute()->fetchAll(\PDO::FETCH_GROUP);
    }
}