<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use SwagMigrationConnector\Controllers\SwagMigrationApiControllerBase;
use SwagMigrationConnector\Service\ControllerReturnStruct;

class Shopware_Controllers_Api_SwagMigrationOrders extends SwagMigrationApiControllerBase
{
    public function indexAction()
    {
        $offset = (int) $this->Request()->getParam('offset', 0);
        $limit = (int) $this->Request()->getParam('limit', 250);
        $orderService = $this->container->get('swag_migration_connector.service.order_service');

        $orders = $orderService->getOrders($offset, $limit);
        $response = new ControllerReturnStruct($orders, empty($orders));

        $this->view->assign($response->jsonSerialize());
    }
}
