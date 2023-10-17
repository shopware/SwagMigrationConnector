<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use SwagMigrationConnector\Controllers\SwagMigrationApiControllerBase;
use SwagMigrationConnector\Service\ControllerReturnStruct;

class Shopware_Controllers_Api_SwagMigrationNumberRanges extends SwagMigrationApiControllerBase
{
    public function indexAction()
    {
        $service = $this->container->get('swag_migration_connector.service.number_range_service');
        $data = $service->getNumberRanges();
        $response = new ControllerReturnStruct($data, true);

        $this->View()->assign($response->jsonSerialize());
    }
}
