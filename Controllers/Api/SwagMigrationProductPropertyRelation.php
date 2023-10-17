<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use SwagMigrationConnector\Controllers\SwagMigrationApiControllerBase;
use SwagMigrationConnector\Service\ControllerReturnStruct;

class Shopware_Controllers_Api_SwagMigrationProductPropertyRelation extends SwagMigrationApiControllerBase
{
    public function indexAction()
    {
        $offset = (int) $this->Request()->getParam('offset', 0);
        $limit = (int) $this->Request()->getParam('limit', 250);
        $productPropertyRelationService = $this->container->get('swag_migration_connector.service.product_property_relation_service');

        $productPropertyRelations = $productPropertyRelationService->getProductPropertyRelations($offset, $limit);
        $response = new ControllerReturnStruct($productPropertyRelations, empty($productPropertyRelations));

        $this->view->assign($response->jsonSerialize());
    }
}
