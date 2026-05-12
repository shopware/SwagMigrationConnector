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
    /**
     * @return void
     */
    public function indexAction()
    {
        $dbConfig = $this->container->getParameter('shopware.db');
        \assert(\is_array($dbConfig));

        $timezone = $dbConfig['timezone'] ?? null;
        \assert($timezone === null || \is_string($timezone));

        if ($timezone === '') {
            $timezone = null;
        }

        $response = new ControllerReturnStruct([
            ['timezone' => $timezone],
        ]);

        $this->view->assign($response->jsonSerialize());
    }
}
