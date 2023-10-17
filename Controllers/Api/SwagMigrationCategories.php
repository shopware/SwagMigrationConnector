<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use SwagMigrationConnector\Controllers\SwagMigrationApiControllerBase;
use SwagMigrationConnector\Service\ControllerReturnStruct;

class Shopware_Controllers_Api_SwagMigrationCategories extends SwagMigrationApiControllerBase
{
    public function indexAction()
    {
        $offset = (int) $this->Request()->getParam('offset', 0);
        $limit = (int) $this->Request()->getParam('limit', 250);
        $categoryService = $this->container->get('swag_migration_connector.service.category_service');

        $categories = $categoryService->getCategories($offset, $limit);
        $response = new ControllerReturnStruct($categories, empty($categories));

        $this->view->assign($response->jsonSerialize());
    }
}
