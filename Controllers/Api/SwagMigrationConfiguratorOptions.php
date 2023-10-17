<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use SwagMigrationConnector\Controllers\SwagMigrationApiControllerBase;
use SwagMigrationConnector\Service\ControllerReturnStruct;

class Shopware_Controllers_Api_SwagMigrationConfiguratorOptions extends SwagMigrationApiControllerBase
{
    public function indexAction()
    {
        $offset = (int) $this->Request()->getParam('offset', 0);
        $limit = (int) $this->Request()->getParam('limit', 250);
        $configuratorOptionService = $this->container->get('swag_migration_connector.service.configurator_option_service');

        $configuratorOptions = $configuratorOptionService->getConfiguratorOptions($offset, $limit);
        $response = new ControllerReturnStruct($configuratorOptions, empty($configuratorOptions));

        $this->view->assign($response->jsonSerialize());
    }
}
