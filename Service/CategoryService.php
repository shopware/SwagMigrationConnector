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
        $mainCategoryLocales = $this->categoryRepository->fetchMainCategoryLocales();

        $categories = $this->mapData($fetchedCategories, [], ['category', 'categorypath', 'previousSiblingId', 'categoryPosition']);

        $resultSet = $this->setAllLocales($categories, $mainCategoryLocales);

        return $this->cleanupResultSet($resultSet);
    }

    /**
     * @return array
     */
    private function setAllLocales(array $categories, array $mainCategoryLocales)
    {
        $resultSet = [];
        $ignoredCategories = $this->categoryRepository->fetchIgnoredCategories();
        $defaultShop = $this->modelManager->getRepository(Shop::class)->getDefault();

        // represents the main language of the migrated shop
        $defaultLocale = \str_replace('_', '-', $defaultShop->getLocale()->getLocale());

        foreach ($categories as $key => $category) {
            $locale = '';
            if (\in_array($category['parent'], $ignoredCategories, true)) {
                $category['parent'] = null;
            }
            if (!empty($category['path'])) {
                $parentCategoryIds = \array_values(
                    \array_filter(\explode('|', $category['path']))
                );
                foreach ($parentCategoryIds as $parentCategoryId) {
                    if (isset($mainCategoryLocales[$parentCategoryId])) {
                        $locale = \str_replace('_', '-', $mainCategoryLocales[$parentCategoryId]);
                        break;
                    }
                }
            }

            if (empty($locale)) {
                $locale = $defaultLocale;
            }
            $category['_locale'] = $locale;
            $resultSet[] = $category;
        }

        return $resultSet;
    }
}
