<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use SwagMigrationConnector\Controllers\SwagMigrationApiControllerBase;
use SwagMigrationConnector\Service\ControllerReturnStruct;

class Shopware_Controllers_Api_SwagMigrationTimeZone extends SwagMigrationApiControllerBase
{
    public function indexAction()
    {
        $timeZone = (string) $this->container->getParameter('shopware.db.timezone');
        if ($timeZone === '') {
            $timeZone = null;
        }

        $this->view->assign((new ControllerReturnStruct(['timezone' => $timeZone]))->jsonSerialize());
    }
}
