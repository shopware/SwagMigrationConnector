<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use SwagMigrationConnector\Controllers\SwagMigrationApiControllerBase;
use SwagMigrationConnector\Service\ControllerReturnStruct;

class Shopware_Controllers_Api_SwagMigrationCrossSelling extends SwagMigrationApiControllerBase
{
    public function indexAction()
    {
        $offset = (int) $this->Request()->getParam('offset', 0);
        $limit = (int) $this->Request()->getParam('limit', 250);
        $crossSellingService = $this->container->get('swag_migration_connector.service.cross_selling_service');

        $crossSelling = $crossSellingService->getCrossSelling($offset, $limit);
        $response = new ControllerReturnStruct($crossSelling, empty($crossSelling));

        $this->view->assign($response->jsonSerialize());
    }
}
