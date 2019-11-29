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

class CategoryRepository extends AbstractRepository
{
    /**
     * {@inheritdoc}
     */
    public function requiredForCount(array $entities)
    {
        return !in_array(DefaultEntities::CATEGORY, $entities);
    }

    /**
     * {@inheritdoc}
     */
    public function getTotal()
    {
        $total = (int) $this->connection->createQueryBuilder()
            ->select('COUNT(*)')
            ->from('s_categories')
            ->where('path IS NOT NULL AND parent IS NOT NULL')
            ->execute()
            ->fetchColumn();

        return new TotalStruct(DefaultEntities::CATEGORY, $total);
    }

    /**
     * {@inheritdoc}
     */
    public function fetch($offset = 0, $limit = 250)
    {
        $query = $this->connection->createQueryBuilder();

        $query->from('s_categories', 'category');
        $this->addTableSelection($query, 's_categories', 'category');
        $query->addSelect('REPLACE(category.path, "|", "") as categorypath');

        $query->leftJoin('category', 's_categories_attributes', 'attributes', 'category.id = attributes.categoryID');
        $this->addTableSelection($query, 's_categories_attributes', 'attributes');

        $query->leftJoin('category', 's_media', 'asset', 'category.mediaID = asset.id');
        $this->addTableSelection($query, 's_media', 'asset');

        $query->leftJoin(
            'category',
            's_categories',
            'sibling',
            'sibling.id = (SELECT previous.id
                           FROM (SELECT sub_category.id, sub_category.parent,
                                        IFNULL(sub_category.position, IFNULL(
                                                                    (SELECT new_position.position + sub_category.id
                                                                     FROM s_categories new_position
                                                                     WHERE sub_category.parent = new_position.parent
                                                                     ORDER BY new_position.position DESC
                                                                     LIMIT 1),
                                                                    sub_category.id)) position
                                 FROM s_categories sub_category) previous
                                 WHERE previous.position < IFNULL(category.position, IFNULL((SELECT previous.position + category.id
                                                                                       FROM s_categories previous
                                                                                       WHERE category.parent = previous.parent
                                                                                       ORDER BY previous.position DESC
                                                                                       LIMIT 1), category.id))
                                 AND category.parent = previous.parent
                                 ORDER BY previous.position DESC
                           LIMIT 1)'
        );
        $query->addSelect('sibling.id as previousSiblingId');
        $query->addSelect('IFNULL(category.position, IFNULL((SELECT previous.position + category.id
                                         FROM s_categories previous
                                         WHERE category.parent = previous.parent
                                         ORDER BY previous.position DESC
                                         LIMIT 1), category.id)) as categoryPosition');
        $query->andWhere('category.parent IS NOT NULL');
        $query->orderBy('LENGTH(categorypath)');
        $query->addOrderBy('category.parent');
        $query->addOrderBy('IFNULL(category.position, IFNULL((SELECT previous.position + category.id
                      FROM s_categories previous
                      WHERE category.parent = previous.parent
                      ORDER BY previous.position DESC
                      LIMIT 1), category.id))');
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

    public function fetchIgnoredCategories()
    {
        $query = $this->connection->createQueryBuilder();

        $query->addSelect('category.id');
        $query->from('s_categories', 'category');
        $query->andWhere('category.parent IS NULL AND category.path IS NULL');

        return $query->execute()->fetchAll(\PDO::FETCH_COLUMN);
    }
}
