<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationApi\Service;

use SwagMigrationApi\Repository\CategoryRepository;

class CategoryService extends AbstractApiService
{
    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    /**
     * @param CategoryRepository $categoryRepository
     */
    public function __construct(CategoryRepository $categoryRepository)
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
        $fetchedCategories = $this->categoryRepository->fetchCategories($offset, $limit);
        $categories = $this->mapData($fetchedCategories, [], ['category', 'locale']);

        return $this->setAllLocales($categories);
    }

    /**
     * @param array $categories
     * @param array $locales
     *
     * @return array
     */
    private function setAllLocales(array &$categories, array &$locales = [])
    {
        foreach ($categories as $key => &$category) {
            if (empty($category['path'])
                && !empty($category['locale'])
                && !isset($locales[$category['id']])
            ) {
                $locales[$category['id']] = $category['locale'];
                unset($categories[$key]);
                $this->setAllLocales($categories, $locales);
            } elseif (empty($category['locale'])) {
                $parentCategoryIds = array_values(
                    array_filter(explode('|', $category['path']))
                );

                foreach ($parentCategoryIds as $parentCategoryId) {
                    if (isset($locales[$parentCategoryId])) {
                        $category['locale'] = $locales[$parentCategoryId];
                    }
                }
                $this->setAllLocales($categories, $locales);
            }
        }

        return array_values($categories);
    }
}
