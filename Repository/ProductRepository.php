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

        $query->leftJoin('product', 's_core_tax', 'product_tax', 'product.taxID = product_tax.id');
        $this->addTableSelection($query, 's_core_tax', 'product_tax');

        $query->leftJoin('product', 's_articles_attributes', 'product_attributes', 'product_detail.id = product_attributes.articledetailsID');
        $this->addTableSelection($query, 's_articles_attributes', 'product_attributes');

        $query->leftJoin('product', 's_articles_supplier', 'product_manufacturer', 'product.supplierID = product_manufacturer.id');
        $this->addTableSelection($query, 's_articles_supplier', 'product_manufacturer');

        $query->addOrderBy('product_detail.kind');

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

        $query->leftJoin('price', 's_core_customergroups', 'price_customergroup', 'price.pricegroup = price_customergroup.groupkey');
        $this->addTableSelection($query, 's_core_customergroups', 'price_customergroup');

        $query->leftJoin('price', 's_articles_prices_attributes', 'price_attributes', 'price.id = price_attributes.id');
        $this->addTableSelection($query, 's_articles_prices_attributes', 'price_attributes');

        $query->where('price.articledetailsID IN (:ids)');
        $query->setParameter('ids', $ids, Connection::PARAM_INT_ARRAY);

        return $query->execute()->fetchAll(\PDO::FETCH_GROUP);
    }

    /**
     * @param array $ids
     *
     * @return array
     */
    public function fetchProductAssets(array $ids)
    {
        $query = $this->getConnection()->createQueryBuilder();

        $query->from('s_articles_img', 'product_asset');
        $query->addSelect('product_asset.articleID');
        $this->addTableSelection($query, 's_articles_img', 'product_asset');

        $query->leftJoin('product_asset', 's_media', 'product_asset_media', 'product_asset.media_id = product_asset_media.id');
        $this->addTableSelection($query, 's_media', 'product_asset_media');

        $query->where('product_asset.articleID IN (:ids)');
        $query->setParameter('ids', $ids, Connection::PARAM_INT_ARRAY);

        return $query->execute()->fetchAll(\PDO::FETCH_GROUP);
    }
}