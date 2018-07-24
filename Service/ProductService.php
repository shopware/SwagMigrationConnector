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
        $products = $this->productRepository->getProducts($offset, $limit);

        $ids = array_column($products, 'product_detail.id');
        $productPrices = $this->productRepository->fetchProductPrices($ids);

        foreach ($products as $key => &$product) {
            if (array_key_exists($product['product_detail.id'], $productPrices)) {
                $product['prices'] = $productPrices[$product['product_detail.id']];
            }
        }
        unset($product);

        return $this->mapData($products);
    }
}