<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Tests\Functional\Controllers;

use Enlight_Controller_Request_RequestTestCase as Request;
use Enlight_Controller_Response_ResponseTestCase as Response;
use Enlight_Template_Manager;
use Enlight_View_Default;
use PHPUnit\Framework\TestCase;
use Shopware\Components\DependencyInjection\Container;

require __DIR__ . '/../../../Controllers/Api/SwagMigrationTimeZone.php';

class SwagMigrationTimeZoneTest extends TestCase
{
    /**
     * @dataProvider getDifferentTimeZones
     *
     * @return void
     */
    public function testIndexActionReturnsConfiguredDatabaseTimeZone($timezone, $expectedTimezone)
    {
        $controller = $this->createController($timezone);

        $controller->indexAction();

        $assign = $controller->View()->getAssign();

        static::assertSame(['timezone' => $expectedTimezone], $assign['data']);
    }

    public function getDifferentTimeZones(): array
    {
        return [
            'empty' => [
                'timezone' => '',
                'expectedTimezone' => null,
            ],
            'NULL' => [
                'timezone' => NULL,
                'expectedTimezone' => NULL,
            ],
            'Europe/Berlin' => [
                'timezone' => 'Europe/Berlin',
                'expectedTimezone' => 'Europe/Berlin',
            ],
            'Europe/London' => [
                'timezone' => 'Europe/London',
                'expectedTimezone' => 'Europe/London',
            ],
            'America/New_York' => [
                'timezone' => 'America/New_York',
                'expectedTimezone' => 'America/New_York',
            ],
        ];
    }

    /**
     * @param string|null $timeZone
     *
     * @return \Shopware_Controllers_Api_SwagMigrationTimeZone
     */
    private function createController($timeZone)
    {
        $container = new Container();
        $container->setParameter('shopware.db.timezone', $timeZone);

        $controller = new \Shopware_Controllers_Api_SwagMigrationTimeZone();
        $controller->setRequest(new Request());
        $controller->setResponse(new Response());
        $controller->setView(new Enlight_View_Default(new Enlight_Template_Manager()));
        $controller->setContainer($container);

        return $controller;
    }
}
