<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use SwagMigrationConnector\Controllers\SwagMigrationApiControllerBase;
use SwagMigrationConnector\Service\ControllerReturnStruct;

class Shopware_Controllers_Api_SwagMigrationOrderNotes extends SwagMigrationApiControllerBase
{
    public function indexAction()
    {
        $offset = (int) $this->Request()->getParam('offset', 0);
        $limit = (int) $this->Request()->getParam('limit', 250);
        $orderNoteService = $this->container->get('swag_migration_connector.service.order_note_service');

        $orderNotes = $orderNoteService->getOrderNotes($offset, $limit);
        $response = new ControllerReturnStruct($orderNotes, empty($orderNotes));

        $this->view->assign($response->jsonSerialize());
    }
}
