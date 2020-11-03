<?php declare(strict_types=1);
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Tests\Functional\Service;

use PHPUnit\Framework\TestCase;

class DispatchServiceTest extends TestCase
{
    public function test_read_should_be_successful()
    {
        $dispatchService = Shopware()->Container()->get('swag_migration_connector.service.dispatch_service');

        $dispatches = $dispatchService->getDispatches();

        $this->assertCount(5, $dispatches);

        $dispatch = $dispatches[0];

        $this->assertSame('9', $dispatch['id']);
        $this->assertArrayHasKey('shippingCountries', $dispatch);
        $this->assertArrayHasKey('paymentMethods', $dispatch);
        $this->assertSame([
            [
                'countryID' => '2',
                'countryiso' => 'DE',
                'iso3' => 'DEU',
            ],
        ], $dispatch['shippingCountries']);
        $this->assertSame(['2', '3', '4', '5'], $dispatch['paymentMethods']);
    }

    public function test_read_with_offset_should_be_successful()
    {
        $dispatchService = Shopware()->Container()->get('swag_migration_connector.service.dispatch_service');

        $dispatches = $dispatchService->getDispatches(1);

        $this->assertCount(4, $dispatches);
    }

    public function test_read_with_limit_should_be_successful()
    {
        $dispatchService = Shopware()->Container()->get('swag_migration_connector.service.dispatch_service');

        $dispatches = $dispatchService->getDispatches(0, 1);

        $this->assertCount(1, $dispatches);
    }

    public function test_read_with_limit_and_offset_should_be_successful()
    {
        $dispatchService = Shopware()->Container()->get('swag_migration_connector.service.dispatch_service');

        $dispatches = $dispatchService->getDispatches(1, 1);

        $this->assertCount(1, $dispatches);
    }

    public function test_read_with_out_of_bounds_offset_should_offer_empty_array()
    {
        $dispatchService = Shopware()->Container()->get('swag_migration_connector.service.dispatch_service');

        $dispatches = $dispatchService->getDispatches(10);

        $this->assertEmpty($dispatches);
    }
}
