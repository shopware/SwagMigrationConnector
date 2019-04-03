<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationAssistant\Repository;

use Doctrine\DBAL\Connection;

class ProductRepository extends AbstractRepository
{
    /**
     * {@inheritdoc}
     */
    public function fetch($offset = 0, $limit = 250)
    {
        $ids = $this->fetchIdentifiers('s_articles_details', $offset, $limit);

        $query = $this->connection->createQueryBuilder();

        $query->from('s_articles_details', 'product_detail');
        $this->addTableSelection($query, 's_articles_details', 'product_detail');

        $query->leftJoin('product_detail', 's_articles', 'product', 'product.id = product_detail.articleID');
        $this->addTableSelection($query, 's_articles', 'product');

        $query->leftJoin('product_detail', 's_core_units', 'unit', 'product_detail.unitID = unit.id');
        $this->addTableSelection($query, 's_core_units', 'unit');

        $query->leftJoin('product', 's_core_tax', 'product_tax', 'product.taxID = product_tax.id');
        $this->addTableSelection($query, 's_core_tax', 'product_tax');

        $query->leftJoin('product', 's_articles_attributes', 'product_attributes', 'product_detail.id = product_attributes.articledetailsID');
        $this->addTableSelection($query, 's_articles_attributes', 'product_attributes');

        $query->leftJoin('product', 's_articles_supplier', 'product_manufacturer', 'product.supplierID = product_manufacturer.id');
        $this->addTableSelection($query, 's_articles_supplier', 'product_manufacturer');

        $query->leftJoin('product_manufacturer', 's_media', 'product_manufacturer_media', 'product_manufacturer.img = product_manufacturer_media.path');
        $this->addTableSelection($query, 's_media', 'product_manufacturer_media');

        $query->where('product_detail.id IN (:ids)');
        $query->setParameter('ids', $ids, Connection::PARAM_STR_ARRAY);

        $query->addOrderBy('product_detail.kind');
        $query->addOrderBy('product_detail.id');

        return $query->execute()->fetchAll();
    }

    /**
     * @param array $productIds
     *
     * @return array
     */
    public function fetchProductCategories(array $productIds)
    {
        $query = $this->connection->createQueryBuilder();

        $query->from('s_categories', 'category');
        $query->addSelect('product_category.articleID');
        $query->addSelect('category.id');

        $query->leftJoin('category', 's_articles_categories', 'product_category', 'category.id = product_category.categoryID');

        $query->where('product_category.articleID IN (:ids)');
        $query->setParameter('ids', $productIds, Connection::PARAM_INT_ARRAY);

        return $query->execute()->fetchAll(\PDO::FETCH_GROUP);
    }

    /**
     * @param array $variantIds
     *
     * @return array
     */
    public function fetchProductPrices(array $variantIds)
    {
        $query = $this->connection->createQueryBuilder();

        $query->from('s_articles_prices', 'price');
        $query->addSelect('price.articledetailsID');
        $this->addTableSelection($query, 's_articles_prices', 'price');

        $query->leftJoin('price', 's_core_customergroups', 'price_customergroup', 'price.pricegroup = price_customergroup.groupkey');
        $this->addTableSelection($query, 's_core_customergroups', 'price_customergroup');

        $query->leftJoin('price', 's_articles_prices_attributes', 'price_attributes', 'price.id = price_attributes.priceID');
        $this->addTableSelection($query, 's_articles_prices_attributes', 'price_attributes');

        $query->where('price.articledetailsID IN (:ids)');
        $query->setParameter('ids', $variantIds, Connection::PARAM_INT_ARRAY);

        return $query->execute()->fetchAll(\PDO::FETCH_GROUP);
    }

    /**
     * @param array $variantIds
     *
     * @return array
     */
    public function fetchProductConfiguratorOptions(array $variantIds)
    {
        $query = $this->connection->createQueryBuilder();

        $query->from('s_article_configurator_options', 'configurator_option');
        $query->addSelect('option_relation.article_id');
        $this->addTableSelection($query, 's_article_configurator_options', 'configurator_option');

        $query->leftJoin('configurator_option', 's_article_configurator_option_relations', 'option_relation', 'option_relation.option_id = configurator_option.id');

        $query->where('option_relation.article_id IN (:ids)');

        $query->setParameter('ids', $variantIds, Connection::PARAM_INT_ARRAY);

        return $query->execute()->fetchAll(\PDO::FETCH_GROUP);
    }

    /**
     * @param array $productIds
     *
     * @return array
     */
    public function fetchProductAssets(array $productIds)
    {
        $query = $this->connection->createQueryBuilder();

        $query->from('s_articles_img', 'asset');
        $query->addSelect('asset.articleID');
        $this->addTableSelection($query, 's_articles_img', 'asset');

        $query->leftJoin('asset', 's_articles_img_attributes', 'asset_attributes', 'asset_attributes.imageID = asset.id');
        $this->addTableSelection($query, 's_articles_img_attributes', 'asset_attributes');

        $query->leftJoin('asset', 's_media', 'asset_media', 'asset.media_id = asset_media.id');
        $this->addTableSelection($query, 's_media', 'asset_media');

        $query->leftJoin('asset_media', 's_media_attributes', 'asset_media_attributes', 'asset_media.id = asset_media_attributes.mediaID');
        $this->addTableSelection($query, 's_media_attributes', 'asset_media_attributes');

        $query->leftJoin('asset_media', 's_media_album', 'asset_media_album', 'asset_media.albumID = asset_media_album.id');
        $this->addTableSelection($query, 's_media_album', 'asset_media_album');

        $query->leftJoin('asset_media_album', 's_media_album_settings', 'asset_media_album_settings', 'asset_media_album.id = asset_media_album_settings.albumID');
        $this->addTableSelection($query, 's_media_album_settings', 'asset_media_album_settings');

        $query->where('asset.articleID IN (:ids)');
        $query->setParameter('ids', $productIds, Connection::PARAM_INT_ARRAY);

        return $query->execute()->fetchAll(\PDO::FETCH_GROUP);
    }

    /**
     * @param array $variantIds
     *
     * @return array
     */
    public function fetchVariantAssets(array $variantIds)
    {
        $query = $this->connection->createQueryBuilder();

        $query->from('s_articles_img', 'asset');
        $query->addSelect('asset.parent_id');
        $this->addTableSelection($query, 's_articles_img', 'asset');

        $query->leftJoin('asset', 's_articles_img_attributes', 'asset_attributes', 'asset_attributes.imageID = asset.id');
        $this->addTableSelection($query, 's_articles_img_attributes', 'asset_attributes');

        $query->where('asset.article_detail_id IN (:ids)');
        $query->setParameter('ids', $variantIds, Connection::PARAM_INT_ARRAY);

        return $query->execute()->fetchAll(\PDO::FETCH_GROUP);
    }

    /**
     * {@inheritdoc}
     */
    protected function fetchIdentifiers($table, $offset = 0, $limit = 250)
    {
        $query = $this->connection->createQueryBuilder();

        $query->select('id');
        $query->from($table);
        $query->addOrderBy('kind');
        $query->addOrderBy('id');

        $query->setFirstResult($offset);
        $query->setMaxResults($limit);

        return $query->execute()->fetchAll(\PDO::FETCH_COLUMN);
    }
}
