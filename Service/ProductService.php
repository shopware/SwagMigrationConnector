<?php

namespace SwagMigrationApi\Service;

use SwagMigrationApi\Repository\ProductRepository;

class ProductService extends AbstractApiService
{
    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @param ProductRepository $productRepository
     */
    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * @param int $offset
     * @param int $limit
     *
     * @return array
     */
    public function getProducts($offset = 0, $limit = 250)
    {
        $fetchedProducts = $this->productRepository->getProducts($offset, $limit);
        $ids = array_column($fetchedProducts, 'product_detail.id');
        $productIds = array_column($fetchedProducts, 'product_detail.articleID');

        $products = $this->mapData($fetchedProducts);

        return $this->assignAssociatedData($products, $ids, $productIds);
    }

    /**
     * @param array $products
     * @param array $detailIds
     * @param array $productIds
     * @return array
     */
    protected function assignAssociatedData(array $products, array $detailIds, array $productIds)
    {
        $productPrices = $this->productRepository->fetchProductPrices($detailIds);
        $prices = $this->mapData($productPrices, [], ['price']);

        foreach ($products as $key => &$product) {
            if (isset($prices[$product['product']['detail']['id']])) {
                $product['product']['prices'] = $prices[$product['product']['detail']['id']];
            }
        }
        unset($product);

        return $products;
    }
}