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
        if (!\is_array($dbConfig)) {
            $this->assignEmptyResult();

            return;
        }

        if (!$this->hasTimezoneConfig($dbConfig)) {
            $this->assignEmptyResult();

            return;
        }

        $timezone = $dbConfig['timezone'];
        if ($timezone === '') {
            $timezone = null;
        }

        $response = new ControllerReturnStruct([
            ['timezone' => $timezone],
        ]);

        $this->view->assign($response->jsonSerialize());
    }

    /**
     * @param array<string, mixed> $dbConfig
     *
     * @return bool
     */
    private function hasTimezoneConfig(array $dbConfig)
    {
        return isset($dbConfig['timezone']);
    }

    /**
     * @return void
     */
    private function assignEmptyResult()
    {
        $response = new ControllerReturnStruct([
            ['timezone' => null],
        ]);

        $this->view->assign($response->jsonSerialize());
    }
}
