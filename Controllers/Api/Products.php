<?php

use SwagMigrationApi\Struct\ProductResult;

class Shopware_Controllers_Api_Products extends Shopware_Controllers_Api_Rest
{
    public function indexAction()
    {
        $productRepository = $this->container->get('shopware.migration_api_endpoint.product.repository');
        $productResult = new ProductResult();

        $products = $productRepository->getProducts();
        echo '<pre>';
        \Doctrine\Common\Util\Debug::dump($products, 5);
        die;
        $productDetails = $productRepository->getProductDetails();

//        $productResult->setProducts()

        $this->view->assign($products);
    }
}