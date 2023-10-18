<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use SwagMigrationConnector\Controllers\SwagMigrationApiControllerBase;
use SwagMigrationConnector\Service\ControllerReturnStruct;

class Shopware_Controllers_Api_SwagMigrationMainVariantRelations extends SwagMigrationApiControllerBase
{
    public function indexAction()
    {
        $offset = (int) $this->Request()->getParam('offset', 0);
        $limit = (int) $this->Request()->getParam('limit', 250);
        $service = $this->container->get('swag_migration_connector.service.main_variant_relation_service');

        $relations = $service->getMainVariantRelations($offset, $limit);
        $response = new ControllerReturnStruct($relations, empty($relations));

        $this->view->assign($response->jsonSerialize());
    }
}
