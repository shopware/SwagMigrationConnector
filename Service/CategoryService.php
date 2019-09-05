<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Service;

use Shopware\Bundle\MediaBundle\MediaServiceInterface;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Shop\Shop;
use SwagMigrationConnector\Repository\ApiRepositoryInterface;
use SwagMigrationConnector\Repository\CategoryRepository;

class CategoryService extends AbstractApiService
{
    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var MediaServiceInterface
     */
    private $mediaService;

    /**
     * @param ApiRepositoryInterface $categoryRepository
     * @param ModelManager           $modelManager
     * @param MediaServiceInterface  $mediaService
     */
    public function __construct(
        ApiRepositoryInterface $categoryRepository,
        ModelManager $modelManager,
        MediaServiceInterface $mediaService
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->modelManager = $modelManager;
        $this->mediaService = $mediaService;
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

        $categories = $this->mapData($fetchedCategories, [], ['category', 'categorypath', 'previousSiblingId', 'categoryPosition']);

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
        $ignoredCategories = $this->categoryRepository->fetchIgnoredCategories();
        $defaultShop = $this->modelManager->getRepository(Shop::class)->getDefault();

        // represents the main language of the migrated shop
        $defaultLocale = str_replace('_', '-', $defaultShop->getLocale()->getLocale());

        foreach ($categories as $key => $category) {
            $locale = '';
            if (in_array($category['parent'], $ignoredCategories, true)) {
                $category['parent'] = null;
            }
            $topMostParent = $category['id'];
            if (!empty($category['path'])) {
                $parentCategoryIds = array_values(
                    array_filter(explode('|', $category['path']))
                );
                $topMostParent = end($parentCategoryIds);
            }
            if (isset($topMostCategories[$topMostParent])) {
                $locale = str_replace('_', '-', $topMostCategories[$topMostParent]);
            }
            if (isset($category['asset'])) {
                $category['asset']['uri'] = $this->mediaService->getUrl($category['asset']['path']);
            }
            if (empty($locale)) {
                $locale = $defaultLocale;
            }
            $category['_locale'] = $locale;
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
            if (empty($category['category.path'])) {
                continue;
            }
            $parentCategoryIds = array_values(
                array_filter(explode('|', (string) $category['category.path']))
            );
            $topMostParent = end($parentCategoryIds);
            if (!in_array($topMostParent, $ids, true)) {
                $ids[] = $topMostParent;
            }
        }

        return $ids;
    }
}
