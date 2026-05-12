<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use SwagMigrationConnector\Controllers\SwagMigrationApiControllerBase;
use SwagMigrationConnector\Service\ControllerReturnStruct;

class Shopware_Controllers_Api_SwagMigrationTimezone extends SwagMigrationApiControllerBase
{

    public function indexAction()
    {
        $dbConfig = $this->container->getParameter('shopware.db');

        $response = new ControllerReturnStruct([
            ['timezone' => $dbConfig['timezone'] ?? null]
        ]);

        $this->view->assign($response->jsonSerialize());
    }
}
