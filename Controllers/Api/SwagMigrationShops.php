<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use SwagMigrationConnector\Controllers\SwagMigrationApiControllerBase;
use SwagMigrationConnector\Service\ControllerReturnStruct;

class Shopware_Controllers_Api_SwagMigrationShops extends SwagMigrationApiControllerBase
{
    public function indexAction()
    {
        $shopService = $this->container->get('swag_migration_connector.service.shop_service');

        $shops = $shopService->getShops();
        $response = new ControllerReturnStruct($shops, true);

        $this->view->assign($response->jsonSerialize());
    }
}
