<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Tests\Functional\Controllers;

use Shopware_Controllers_Api_SwagMigrationTimezone as SwagMigrationTimezone;
use SwagMigrationConnector\Tests\Functional\ContainerTrait;
use SwagMigrationConnector\Tests\Functional\Controllers\ControllerFactory\Arguments;
use SwagMigrationConnector\Tests\Functional\Controllers\ControllerFactory\ControllerFactory;

require __DIR__ . '/../../../Controllers/Api/SwagMigrationTimezone.php';

class SwagMigrationTimezoneTest extends \Enlight_Components_Test_Controller_TestCase
{
    use ContainerTrait;

    /**
     * @return void
     */
    public function testIndexActionReturnsConfiguredDatabaseTimezone()
    {
        $container = $this->getContainer();
        $dbConfig = $container->getParameter('shopware.db');
        static::assertIsArray($dbConfig);

        $configuredTimezone = $dbConfig['timezone'];
        $timezone = $configuredTimezone === '' ? null : $configuredTimezone;

        $controller = ControllerFactory::createController(
            SwagMigrationTimezone::class,
            new Arguments($container)
        );

        $controller->indexAction();

        static::assertSame([
            'data' => [
                [
                    'timezone' => $timezone,
                ],
            ],
            'isLastRequest' => false,
            'success' => true,
        ], $controller->View()->getAssign());
    }
}
