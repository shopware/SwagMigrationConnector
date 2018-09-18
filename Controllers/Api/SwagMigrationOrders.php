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
class Shopware_Controllers_Api_SwagMigrationOrders extends Shopware_Controllers_Api_SwagMigrationApi
{
    public function indexAction()
    {
        $offset = (int) $this->Request()->getParam('offset', 0);
        $limit = (int) $this->Request()->getParam('limit', 250);
        $orderService = $this->container->get('swag_migration_api.service.order_service');

        $orders = $orderService->getOrders($offset, $limit);

        $this->view->assign(['success' => true, 'data' => $orders]);
    }
}
