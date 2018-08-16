<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationApi\Repository;

use Doctrine\DBAL\Connection;

class CategoryRepository extends AbstractRepository
{
    /**
     * {@inheritdoc}
     */
    public function fetch($offset = 0, $limit = 250)
    {
        $query = $this->connection->createQueryBuilder();

        $query->from('s_categories', 'category');
        $this->addTableSelection($query, 's_categories', 'category');

        $query->leftJoin('category', 's_core_shops', 'shop', 'category.id = shop.category_id');
        $query->leftJoin('shop', 's_core_locales', 'locale', 'locale.id = shop.locale_id');
        $query->addSelect('locale.locale');

        $query->leftJoin('category', 's_categories_attributes', 'attributes', 'category.id = attributes.categoryID');
        $this->addTableSelection($query, 's_categories_attributes', 'attributes');

        $query->leftJoin('category', 's_media', 'asset', 'category.mediaID = asset.id');
        $this->addTableSelection($query, 's_media', 'asset');

        $query->andWhere('category.parent IS NOT NULL OR category.path IS NOT NULL');

        $query->orderBy('category.parent');
        $query->setFirstResult($offset);
        $query->setMaxResults($limit);

        return $query->execute()->fetchAll();
    }

    /**
     * @param array $topMostParentIds
     *
     * @return array
     */
    public function fetchCategoriesById(array $topMostParentIds)
    {
        $query = $this->connection->createQueryBuilder();

        $query->from('s_categories', 'category');
        $query->addSelect('category.id');

        $query->leftJoin('category', 's_core_shops', 'shop', 'category.id = shop.category_id');
        $query->leftJoin('shop', 's_core_locales', 'locale', 'locale.id = shop.locale_id');
        $query->addSelect('locale.locale');

        $query->where('category.id IN (:ids)');
        $query->setParameter('ids', $topMostParentIds, Connection::PARAM_INT_ARRAY);

        $query->orderBy('category.parent');

        return $query->execute()->fetchAll(\PDO::FETCH_KEY_PAIR);
    }
}
