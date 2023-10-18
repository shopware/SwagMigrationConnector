<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use SwagMigrationConnector\Controllers\SwagMigrationApiControllerBase;
use SwagMigrationConnector\Service\ControllerReturnStruct;

class Shopware_Controllers_Api_SwagMigrationVotes extends SwagMigrationApiControllerBase
{
    public function indexAction()
    {
        $offset = (int) $this->Request()->getParam('offset', 0);
        $limit = (int) $this->Request()->getParam('limit', 250);
        $voteService = $this->container->get('swag_migration_connector.service.vote_service');

        $votes = $voteService->getVotes($offset, $limit);
        $response = new ControllerReturnStruct($votes, empty($votes));

        $this->view->assign($response->jsonSerialize());
    }
}
