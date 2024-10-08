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
use SwagMigrationConnector\Repository\ProductRepository;
use SwagMigrationConnector\Repository\Result\ProductRepository\MainCategoryShopRelationResult;
use SwagMigrationConnector\Repository\Result\ProductRepository\ProductVisibilityResult;
use SwagMigrationConnector\Repository\Result\ProductRepository\ShopCategoryRelation;
use Symfony\Component\HttpFoundation\ParameterBag;

class ProductService extends AbstractApiService
{
    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var MediaServiceInterface
     */
    private $mediaService;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var ParameterBag
     */
    private $productMapping;

    public function __construct(
        ApiRepositoryInterface $productRepository,
        MediaServiceInterface $mediaService,
        ModelManager $modelManager
    ) {
        $this->productRepository = $productRepository;
        $this->mediaService = $mediaService;
        $this->modelManager = $modelManager;

        /* @var ParameterBag */
        $this->productMapping = new ParameterBag();
    }

    /**
     * @param int $offset
     * @param int $limit
     *
     * @return array
     */
    public function getProducts($offset = 0, $limit = 250)
    {
        $fetchedProducts = $this->productRepository->fetch($offset, $limit);

        $this->buildIdentifierMappings($fetchedProducts);

        $resultSet = $this->appendAssociatedData(
            $this->mapData(
                $fetchedProducts,
                [],
                ['product']
            )
        );

        return $this->cleanupResultSet($resultSet);
    }

    protected function buildIdentifierMappings(array $fetchedProducts)
    {
        foreach ($fetchedProducts as $product) {
            $this->productMapping->set($product['product_detail.id'], $product['product.id']);
        }
    }

    /**
     * @return array
     */
    protected function appendAssociatedData(array $products)
    {
        $productIds = $this->getProductIds();

        $categories = $this->productRepository->fetchProductCategories($productIds);
        $productVisibility = $this->getProductVisibility($categories, $this->fetchMainCategoryShops());

        $prices = $this->getPrices();
        $assets = $this->getAssets();
        $options = $this->getConfiguratorOptions();
        $filterValues = $this->getFilterOptionValues();
        $esdPath = $this->productRepository->getEsdConfig();
        $esdFiles = $this->productRepository->fetchEsdFiles($this->productMapping->keys());
        $seoMainCategories = $this->productRepository->fetchProductSeoMainCategories($productIds);

        /** @var Shop $defaultShop */
        $defaultShop = $this->modelManager->getRepository(Shop::class)->getDefault();

        // represents the main language of the migrated shop
        $locale = \str_replace('_', '-', $defaultShop->getLocale()->getLocale());

        foreach ($products as $key => &$product) {
            $product['_locale'] = $locale;
            $product['assets'] = [];

            if (isset($categories[$product['id']])) {
                $product['categories'] = $categories[$product['id']];
            }
            if (isset($seoMainCategories[$product['id']])) {
                $product['mainCategories'] = $seoMainCategories[$product['id']];
            }
            if (isset($prices[$product['detail']['id']])) {
                $product['prices'] = $prices[$product['detail']['id']];
            }
            if (isset($assets[$product['id']][$product['detail']['id']])) {
                $productAssets = $assets[$product['id']][$product['detail']['id']];
                $product['assets'] = $this->prepareAssets($productAssets);
            }
            if (isset($assets['general'][$product['id']])) {
                $generalAssets = $this->prepareAssets($assets['general'][$product['id']]);
                $product['assets'] = \array_merge($product['assets'], $generalAssets);
            }
            if (isset($options[$product['detail']['id']])) {
                $product['configuratorOptions'] = $options[$product['detail']['id']];
            }
            if (isset($product['manufacturer']['media']['id'])) {
                $product['manufacturer']['media']['uri'] = $this->mediaService->getUrl($product['manufacturer']['img']);
            }
            if (isset($filterValues[$product['detail']['id']])) {
                $product['filters'] = $filterValues[$product['detail']['id']];
            }
            if (isset($esdFiles[$product['detail']['id']])) {
                $product['esdFiles'] = \array_values($esdFiles[$product['detail']['id']]);
                foreach ($product['esdFiles'] as &$esdFile) {
                    $esdFile['path'] = $esdPath;
                }
            }

            $product['shops'] = $productVisibility->getShops($product['id']);
        }
        unset(
            $product, $categories, $seoMainCategories,
            $prices, $assets, $options, $esdFile
        );

        $this->productMapping->replace([]);

        return $products;
    }

    /**
     * @return array<int, string>
     */
    private function getProductIds()
    {
        return \array_values(
            $this->productMapping->getIterator()->getArrayCopy()
        );
    }

    /**
     * @return array
     */
    private function getPrices()
    {
        $variantIds = $this->productMapping->keys();
        $fetchedPrices = $this->productRepository->fetchProductPrices($variantIds);

        return $this->mapData($fetchedPrices, [], ['price', 'currencyShortName']);
    }

    /**
     * @return array
     */
    private function getAssets()
    {
        $productIds = \array_values(
            $this->productMapping->getIterator()->getArrayCopy()
        );
        $variantIds = $this->productMapping->keys();

        $fetchedUnlinkedAssets = $this->mapData($this->productRepository->fetchProductAssets($productIds), [], ['asset']);
        $fetchedVariantAssets = $this->mapData($this->productRepository->fetchVariantAssets($variantIds), [], ['asset', 'img', 'description', 'main', 'position']);

        $assets = [];
        foreach ($fetchedVariantAssets as $articleId => $productAssets) {
            if (!isset($assets[$articleId])) {
                $assets[$articleId] = [];
            }

            foreach ($productAssets as $productAsset) {
                if (!isset($productAsset['article_detail_id'])) {
                    continue;
                }

                if (!isset($assets[$articleId][$productAsset['article_detail_id']])) {
                    $assets[$articleId][$productAsset['article_detail_id']] = [];
                }
                $assets[$articleId][$productAsset['article_detail_id']][] = $productAsset;
            }
        }

        $assets['general'] = $fetchedUnlinkedAssets;
        unset($fetchedUnlinkedAssets, $fetchedVariantAssets);

        return $assets;
    }

    /**
     * @return array
     */
    private function getConfiguratorOptions()
    {
        $variantIds = $this->productMapping->keys();
        $fetchedConfiguratorOptions = $this->productRepository->fetchProductConfiguratorOptions($variantIds);

        return $this->mapData($fetchedConfiguratorOptions, [], ['configurator', 'option']);
    }

    /**
     * @return array
     */
    private function getFilterOptionValues()
    {
        $variantIds = $this->productMapping->keys();
        $fetchedConfiguratorOptions = $this->productRepository->fetchFilterOptionValues($variantIds);

        return $this->mapData($fetchedConfiguratorOptions, [], ['filter', 'values']);
    }

    /**
     * @return array
     */
    private function prepareAssets(array $assets)
    {
        foreach ($assets as &$asset) {
            $asset['media']['uri'] = $this->mediaService->getUrl($asset['media']['path']);
        }
        unset($asset);

        return $assets;
    }

    /**
     * @return MainCategoryShopRelationResult
     */
    private function fetchMainCategoryShops()
    {
        $result = new MainCategoryShopRelationResult();
        foreach ($this->productRepository->fetchMainCategoryShops() as $shopCategoryRelation) {
            $result->add(new ShopCategoryRelation($shopCategoryRelation));
        }

        return $result;
    }

    /**
     * @return ProductVisibilityResult
     */
    private function getProductVisibility(array $categories, MainCategoryShopRelationResult $mainCategoryShops)
    {
        $productVisibility = new ProductVisibilityResult();

        foreach ($categories as $productId => $productCategories) {
            foreach ($productCategories as $category) {
                foreach ($this->getParentCategoryIds($category) as $parentCategoryId) {
                    if ($mainCategoryShops->containsCategory($parentCategoryId)) {
                        $productVisibility->add((int) $productId, $mainCategoryShops->getShopIds($parentCategoryId));
                    }
                }
            }
        }

        return $productVisibility;
    }

    /**
     * @param array<string, mixed> $category
     *
     * @return array<int, string>
     */
    private function getParentCategoryIds(array $category)
    {
        if (empty($category['path'])) {
            return [];
        }

        return \array_filter(\explode('|', $category['path']));
    }
}
