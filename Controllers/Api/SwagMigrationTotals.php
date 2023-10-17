<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use SwagMigrationConnector\Controllers\SwagMigrationApiControllerBase;
use SwagMigrationConnector\Service\ControllerReturnStruct;

class Shopware_Controllers_Api_SwagMigrationTotals extends SwagMigrationApiControllerBase
{
    public function indexAction()
    {
        $countInfos = $this->Request()->getParam('countInfos', []);
        $totalsService = $this->container->get('swag_migration_connector.service.totals_service');
        $totalResult = $totalsService->fetchTotals($countInfos);

        $response = new ControllerReturnStruct($totalResult, true);

        $this->view->assign($response->jsonSerialize());
    }
}
