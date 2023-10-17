<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use SwagMigrationConnector\Controllers\SwagMigrationApiControllerBase;
use SwagMigrationConnector\Service\ControllerReturnStruct;

class Shopware_Controllers_Api_SwagMigrationVouchers extends SwagMigrationApiControllerBase
{
    public function indexAction()
    {
        $offset = (int) $this->Request()->getParam('offset', 0);
        $limit = (int) $this->Request()->getParam('limit', 250);
        $customerService = $this->container->get('swag_migration_connector.service.voucher_service');

        $vouchers = $customerService->getVouchers($offset, $limit);
        $response = new ControllerReturnStruct($vouchers, empty($vouchers));

        $this->view->assign($response->jsonSerialize());
    }
}
