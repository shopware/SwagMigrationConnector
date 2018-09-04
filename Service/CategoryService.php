<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationApi\Service;

use SwagMigrationApi\Repository\ApiRepositoryInterface;
use SwagMigrationApi\Repository\CategoryRepository;

class CategoryService extends AbstractApiService
{
    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    /**
     * @param ApiRepositoryInterface $categoryRepository
     */
    public function __construct(ApiRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @param int $offset
     * @param int $limit
     *
     * @return array
     */
    public function getCategories($offset = 0, $limit = 250)
    {
        $fetchedCategories = $this->categoryRepository->fetch($offset, $limit);

        $topMostParentIds = $this->getTopMostParentIds($fetchedCategories);
        $topMostCategories = $this->categoryRepository->fetchCategoriesById($topMostParentIds);

        $categories = $this->mapData($fetchedCategories, [], ['category', 'locale']);

        $resultSet = $this->setAllLocales($categories, $topMostCategories);

        return $this->cleanupResultSet($resultSet);
    }

    /**
     * @param array $categories
     * @param array $topMostCategories
     *
     * @return array
     */
    private function setAllLocales(array $categories, array $topMostCategories)
    {
        $resultSet = [];
        $ignoredNodes = $this->categoryRepository->fetchIgnoredCategories();
        foreach ($categories as $key => $category) {
            if (empty($category['path'])) {
                $ignoredNodes[] = $category['id'];
                continue;
            }
            if (in_array($category['parent'], $ignoredNodes)) {
                $category['parent'] = null;
            }
            $parentCategoryIds = array_values(
                array_filter(explode('|', $category['path']))
            );
            $topMostParent = end($parentCategoryIds);
            $category['_locale'] = $topMostCategories[$topMostParent];
            $resultSet[] = $category;
        }

        return $resultSet;
    }

    /**
     * @param array $categories
     *
     * @return array
     */
    private function getTopMostParentIds(array $categories)
    {
        $ids = [];
        foreach ($categories as $key => $category) {
            $parentCategoryIds = array_values(
                array_filter(explode('|', $category['category.path']))
            );

            $topMostParent = end($parentCategoryIds);
            if (!in_array($topMostParent, $ids)) {
                $ids[] = $topMostParent;
            }
        }

        return $ids;
    }
}
