<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Shopware_Controllers_Api_SwagMigrationProducts extends Shopware_Controllers_Api_SwagMigrationApi
{
    public function indexAction()
    {
        $offset = (int) $this->Request()->getParam('offset', 0);
        $limit = (int) $this->Request()->getParam('limit', 250);
        $productService = $this->container->get('swag_migration_api.service.product_service');

        $products = $productService->getProducts($offset, $limit);

        $this->view->assign([
            'success' => true,
            'data' => $products,
        ]);
    }
}
