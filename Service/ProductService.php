<?php

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
    protected function assignAssociatedData(array $products, array $detailIds, array $productIds)
    {
        $productPrices = $this->productRepository->fetchProductPrices($detailIds);
        $prices = $this->mapData($productPrices, [], ['price']);

        $productAssets = $this->productRepository->fetchProductAssets($productIds);
        $assets = $this->mapData($productAssets, [], ['asset']);

        foreach ($products as $key => &$product) {
            if (isset($prices[$product['detail']['id']])) {
                $product['prices'] = $prices[$product['detail']['id']];
            }
            if (isset($assets[$product['id']])) {
                $assets = $assets[$product['id']];
                $product['assets'] = $this->prepareAssets($assets);
            }
        }
        unset($product);

        return $products;
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