<?php

class Shopware_Controllers_Api_SwagMigrationProducts extends Shopware_Controllers_Api_Rest
{
    public function indexAction()
    {
        $offset = (int) $this->Request()->getParam('start', 0);
        $limit = (int) $this->Request()->getParam('limit', 250);
        $productService = $this->container->get('swag_migration_api.service.product_service');

        $products = $productService->getProducts($offset, $limit);

        $this->view->assign(['success' => true, 'data' => $products]);
    }
}