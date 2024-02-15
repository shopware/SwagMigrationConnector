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

class ProductRepository extends AbstractRepository
{
    /**
     * {@inheritdoc}
     */
    public function requiredForCount(array $entities)
    {
        return !\in_array(DefaultEntities::PRODUCT, $entities);
    }

    /**
     * {@inheritdoc}
     */
    public function getTotal()
    {
        $total = (int) $this->connection->createQueryBuilder()
            ->select('COUNT(*)')
            ->from('s_articles_details')
            ->execute()
            ->fetchColumn();

        return new TotalStruct(DefaultEntities::PRODUCT, $total);
    }

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

        $query->leftJoin('product_manufacturer', 's_articles_supplier_attributes', 'product_manufacturer_attributes', 'product_manufacturer.id = product_manufacturer_attributes.supplierID');
        $this->addTableSelection($query, 's_articles_supplier_attributes', 'product_manufacturer_attributes');

        $query->where('product_detail.id IN (:ids)');
        $query->setParameter('ids', $ids, Connection::PARAM_STR_ARRAY);

        $query->addOrderBy('product_detail.kind');
        $query->addOrderBy('product_detail.id');

        return $query->execute()->fetchAll();
    }

    /**
     * @return array
     */
    public function fetchMainCategoryShops()
    {
        $query = $this->connection->createQueryBuilder();

        $query->from('s_core_shops', 'shop');
        $query->addSelect('shop.category_id, IFNULL(shop.main_id, shop.id)');

        return $query->execute()->fetchAll(\PDO::FETCH_KEY_PAIR);
    }

    /**
     * @param array<string> $variantIds
     *
     * @return array<array<mixed>>
     */
    public function fetchEsdFiles(array $variantIds)
    {
        $query = $this->connection->createQueryBuilder();

        $query->addSelect('esd.articledetailsID, esd.id, esd.file as name');
        $query->from('s_articles_esd', 'esd');

        $query->where('esd.articledetailsID IN (:ids)');
        $query->setParameter('ids', $variantIds, Connection::PARAM_INT_ARRAY);

        return $query->execute()->fetchAll(\PDO::FETCH_GROUP);
    }

    /**
     * @return string|null
     */
    public function getEsdConfig()
    {
        $query = $this->connection->createQueryBuilder();

        $query->select('ifnull(currentConfig.value, defaultConfig.value) as configValue');
        $query->from('s_core_config_elements', 'defaultConfig');

        $query->leftJoin('defaultConfig', 's_core_config_values', 'currentConfig', 'defaultConfig.id =  currentConfig.element_id');

        $query->where('defaultConfig.name = :configName');
        $query->setParameter('configName', 'esdKey');

        return $query->execute()->fetch(\PDO::FETCH_COLUMN);
    }

    /**
     * @param array<string> $productIds
     *
     * @return array<string>
     */
    public function fetchProductCategories(array $productIds)
    {
        $query = $this->connection->createQueryBuilder();

        $query->from('s_articles_categories', 'product_category');

        $query->leftJoin('product_category', 's_categories', 'category', 'category.id = product_category.categoryID');
        $query->addSelect('product_category.articleID', 'product_category.categoryID as id, category.path');

        $query->where('product_category.articleID IN (:ids)');
        $query->setParameter('ids', $productIds, Connection::PARAM_INT_ARRAY);

        return $query->execute()->fetchAll(\PDO::FETCH_GROUP);
    }

    /**
     * @param array<string> $categories
     *
     * @return array<string>
     */
    public function fetchShopsByCategories(array $categories)
    {
        $query = $this->connection->createQueryBuilder();

        $query->from('s_categories', 'category');
        $query->addSelect('category.id');

        $query->innerJoin('category', 's_core_shops', 'shop', 'category.id = shop.category_id');
        $query->addSelect('IFNULL(shop.main_id, shop.id) AS "id"');

        $query->where('category.id IN (:ids)');
        $query->setParameter('ids', $categories, Connection::PARAM_INT_ARRAY);

        return $query->execute()->fetchAll(\PDO::FETCH_GROUP);
    }

    /**
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

        $query->leftJoin('price', 's_core_currencies', 'currency', 'currency.standard = 1');
        $query->addSelect('currency.currency as currencyShortName');

        $query->where('price.articledetailsID IN (:ids)');
        $query->setParameter('ids', $variantIds, Connection::PARAM_INT_ARRAY);

        return $query->execute()->fetchAll(\PDO::FETCH_GROUP);
    }

    /**
     * @return array
     */
    public function fetchProductConfiguratorOptions(array $variantIds)
    {
        $query = $this->connection->createQueryBuilder();

        $query->from('s_article_configurator_options', 'configurator_option');
        $query->addSelect('option_relation.article_id');
        $this->addTableSelection($query, 's_article_configurator_options', 'configurator_option');

        $query->leftJoin('configurator_option', 's_article_configurator_option_relations', 'option_relation', 'option_relation.option_id = configurator_option.id');

        $query->leftJoin('configurator_option', 's_article_configurator_groups', 'configurator_option_group', 'configurator_option.group_id = configurator_option_group.id');
        $this->addTableSelection($query, 's_article_configurator_groups', 'configurator_option_group');

        $query->where('option_relation.article_id IN (:ids)');

        $query->setParameter('ids', $variantIds, Connection::PARAM_INT_ARRAY);

        return $query->execute()->fetchAll(\PDO::FETCH_GROUP);
    }

    /**
     * @return array
     */
    public function fetchFilterOptionValues(array $variantIds)
    {
        $query = $this->connection->createQueryBuilder();

        $query->from('s_filter_articles', 'filter');
        $query->leftJoin('filter', 's_articles_details', 'details', 'details.articleID = filter.articleID');
        $query->addSelect('details.id');

        $query->leftJoin('filter', 's_filter_values', 'filter_values', 'filter.valueID = filter_values.id');
        $this->addTableSelection($query, 's_filter_values', 'filter_values');

        $query->leftJoin('filter_values', 's_filter_options', 'filter_values_option', 'filter_values_option.id = filter_values.optionID');
        $this->addTableSelection($query, 's_filter_options', 'filter_values_option');

        $query->where('details.id IN (:ids)');

        $query->setParameter('ids', $variantIds, Connection::PARAM_INT_ARRAY);

        return $query->execute()->fetchAll(\PDO::FETCH_GROUP);
    }

    /**
     * @return array
     */
    public function fetchProductAssets(array $productIds)
    {
        $query = $this->connection->createQueryBuilder();

        $query->from('s_articles_img', 'asset');
        $query->addSelect('asset.articleID');
        $this->addTableSelection($query, 's_articles_img', 'asset');

        $query->leftJoin('asset', 's_articles_img', 'variantAsset', 'variantAsset.parent_id = asset.id');

        $query->leftJoin('asset', 's_articles_img_attributes', 'asset_attributes', 'asset_attributes.imageID = asset.id');
        $this->addTableSelection($query, 's_articles_img_attributes', 'asset_attributes');

        $query->leftJoin('asset', 's_media', 'asset_media', 'asset.media_id = asset_media.id');
        $this->addTableSelection($query, 's_media', 'asset_media');

        $query->leftJoin('asset_media', 's_media_attributes', 'asset_media_attributes', 'asset_media.id = asset_media_attributes.mediaID');
        $this->addTableSelection($query, 's_media_attributes', 'asset_media_attributes');

        $query->where('asset.articleID IN (:ids) AND variantAsset.id IS NULL');
        $query->setParameter('ids', $productIds, Connection::PARAM_INT_ARRAY);

        return $query->execute()->fetchAll(\PDO::FETCH_GROUP);
    }

    /**
     * @return array
     */
    public function fetchVariantAssets(array $variantIds)
    {
        $query = $this->connection->createQueryBuilder();

        $query->from('s_articles_img', 'asset');
        $query->addSelect('parentasset.articleID');
        $this->addTableSelection($query, 's_articles_img', 'asset');
        $query->addSelect('parentasset.img as img, parentasset.description as description');
        $query->addSelect('parentasset.main as main, parentasset.position as position');

        $query->leftJoin('asset', 's_articles_img_attributes', 'asset_attributes', 'asset_attributes.imageID = asset.id');
        $this->addTableSelection($query, 's_articles_img_attributes', 'asset_attributes');

        $query->leftJoin('asset', 's_articles_img', 'parentasset', 'asset.parent_id = parentasset.id');

        $query->leftJoin('asset', 's_media', 'asset_media', 'parentasset.media_id = asset_media.id');
        $this->addTableSelection($query, 's_media', 'asset_media');

        $query->leftJoin('asset_media', 's_media_attributes', 'asset_media_attributes', 'asset_media.id = asset_media_attributes.mediaID');
        $this->addTableSelection($query, 's_media_attributes', 'asset_media_attributes');

        $query->where('asset.article_detail_id IN (:ids)');
        $query->setParameter('ids', $variantIds, Connection::PARAM_INT_ARRAY);

        return $query->execute()->fetchAll(\PDO::FETCH_GROUP);
    }

    /**
     * {@inheritdoc}
     */
    protected function fetchIdentifiers($table, $offset = 0, $limit = 250, $where = [])
    {
        $query = $this->connection->createQueryBuilder();

        $query->select('id');
        $query->from($table);
        $query->addOrderBy('kind');
        $query->addOrderBy('id');

        foreach ($where as $clause) {
            $query->andWhere($clause);
        }

        $query->setFirstResult($offset);
        $query->setMaxResults($limit);

        return $query->execute()->fetchAll(\PDO::FETCH_COLUMN);
    }
}
