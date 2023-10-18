<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use SwagMigrationConnector\Controllers\SwagMigrationApiControllerBase;
use SwagMigrationConnector\Service\ControllerReturnStruct;

class Shopware_Controllers_Api_SwagMigrationEnvironment extends SwagMigrationApiControllerBase
{
    public function indexAction()
    {
        $environmentService = $this->container->get('swag_migration_connector.service.environment_service');

        $data = $environmentService->getEnvironmentInformation();
        $response = new ControllerReturnStruct($data);

        $this->view->assign($response->jsonSerialize());
    }
}
