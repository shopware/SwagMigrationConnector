<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationApi\Service;

use Shopware\Bundle\MediaBundle\MediaService;
use SwagMigrationApi\Repository\ProductRepository;

class ProductService extends AbstractApiService
{
    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var MediaService
     */
    private $mediaService;

    /**
     * @param ProductRepository $productRepository
     * @param MediaService      $mediaService
     */
    public function __construct(
        ProductRepository $productRepository,
        MediaService $mediaService
    ) {
        $this->productRepository = $productRepository;
        $this->mediaService = $mediaService;
    }

    /**
     * @param int $offset
     * @param int $limit
     *
     * @return array
     */
    public function getProducts($offset = 0, $limit = 250)
    {
        $fetchedProducts = $this->productRepository->fetchProducts($offset, $limit);
        $ids = array_column($fetchedProducts, 'product_detail.id');
        $productIds = array_column($fetchedProducts, 'product_detail.articleID');

        $products = $this->mapData($fetchedProducts, [], ['product']);

        return $this->assignAssociatedData($products, $ids, $productIds);
    }

    /**
     * @param array $products
     * @param array $detailIds
     * @param array $productIds
     *
     * @return array
     */
    protected function assignAssociatedData(array $products, array $variantIds, array $productIds)
    {
        $categories = $this->getCategories($productIds);

        $prices = $this->getPrices($variantIds);
        $productTranslations = $this->getProductTranslations($productIds);
        $variantTranslations = $this->getVariantTranslations($variantIds);
        $assets = $this->getAssets($productIds);
        $options = $this->getConfiguratorOptions($variantIds);

        foreach ($products as $key => &$product) {
            if (isset($categories[$product['id']])) {
                $product['categories'] = $categories[$product['id']];
            }
            if (isset($prices[$product['detail']['id']])) {
                $product['prices'] = $prices[$product['detail']['id']];
            }
            if (isset($productTranslations[$product['id']])) {
                $product['translations'] = $productTranslations[$product['id']];
            }
            if (isset($variantTranslations[$product['detail']['id']])) {
                $product['detail']['translations'] = $variantTranslations[$product['detail']['id']];
            }
            if (isset($assets[$product['id']])) {
                $productAssets = $assets[$product['id']];
                $product['assets'] = $this->prepareAssets($productAssets);
            }
            if (isset($options[$product['detail']['id']])) {
                $product['configuratorOptions'] = $options[$product['detail']['id']];
            }
        }
        unset(
            $product, $categories,
            $prices, $assets, $options
        );

        return $products;
    }

    /**
     * @param array $productIds
     *
     * @return array
     */
    private function getCategories(array $productIds)
    {
        $fetchedCategories = $this->productRepository->fetchProductCategories($productIds);

        return $this->mapData($fetchedCategories, [], ['category', 'id']);
    }

    /**
     * @param array $variantIds
     *
     * @return array
     */
    private function getPrices(array $variantIds)
    {
        $fetchedPrices = $this->productRepository->fetchProductPrices($variantIds);

        return $this->mapData($fetchedPrices, [], ['price']);
    }

    /**
     * @param $productIds
     *
     * @return array
     */
    private function getProductTranslations(array $productIds)
    {
        $fetchedProductTranslations = $this->productRepository->fetchProductTranslations($productIds);

        return $this->mapData($fetchedProductTranslations, [], ['translation', 'locale']);
    }

    /**
     * @param array $variantIds
     *
     * @return array
     */
    private function getVariantTranslations(array $variantIds)
    {
        $fetchedVariantTranslations = $this->productRepository->fetchVariantTranslations($variantIds);

        return $this->mapData($fetchedVariantTranslations, [], ['translation', 'locale']);
    }

    /**
     * @param array $productIds
     *
     * @return array
     */
    private function getAssets(array $productIds)
    {
        $fetchedAssets = $this->productRepository->fetchProductAssets($productIds);

        return $this->mapData($fetchedAssets, [], ['asset']);
    }

    /**
     * @param array $variantIds
     *
     * @return array
     */
    private function getConfiguratorOptions(array $variantIds)
    {
        $fetchedConfiguratorOptions = $this->productRepository->fetchProductConfiguratorOptions($variantIds);

        return $this->mapData($fetchedConfiguratorOptions, [], ['configurator', 'option']);
    }

    /**
     * @param array $assets
     *
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
}
