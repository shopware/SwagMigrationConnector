<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Tests\Functional\Controllers;

use Shopware\Components\DependencyInjection\Container;
use Shopware_Controllers_Api_SwagMigrationTimezone as SwagMigrationTimezone;
use SwagMigrationConnector\Tests\Functional\ContainerTrait;
use SwagMigrationConnector\Tests\Functional\Controllers\ControllerFactory\Arguments;
use SwagMigrationConnector\Tests\Functional\Controllers\ControllerFactory\ControllerFactory;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

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
        static::assertTrue(\is_array($dbConfig));

        $configuredTimezone = $dbConfig['timezone'];
        $timezone = $configuredTimezone === '' ? null : $configuredTimezone;

        $controller = ControllerFactory::createController(
            SwagMigrationTimezone::class,
            new Arguments($container)
        );

        $controller->indexAction();

        $this->assertTimezoneResponse($timezone, $controller);
    }

    /**
     * @return void
     */
    public function testIndexActionReturnsNullTimezoneWhenDatabaseConfigHasNoTimezone()
    {
        $controller = $this->createControllerWithDatabaseConfig([]);
        $controller->indexAction();

        $this->assertTimezoneResponse(null, $controller);
    }

    /**
     * @return void
     */
    public function testIndexActionReturnsNullTimezoneWhenDatabaseConfigIsNotArray()
    {
        $controller = $this->createControllerWithDatabaseConfig('invalid');
        $controller->indexAction();

        $this->assertTimezoneResponse(null, $controller);
    }

    /**
     * @return SwagMigrationTimezone
     */
    private function createControllerWithDatabaseConfig($dbConfig)
    {
        $container = new Container(new ParameterBag([
            'shopware.db' => $dbConfig,
        ]));

        return ControllerFactory::createController(
            SwagMigrationTimezone::class,
            new Arguments($container)
        );
    }

    /**
     * @param string|null           $timezone
     * @param SwagMigrationTimezone $controller
     *
     * @return void
     */
    private function assertTimezoneResponse($timezone, $controller)
    {
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
