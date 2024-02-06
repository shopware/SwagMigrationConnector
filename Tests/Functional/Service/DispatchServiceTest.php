<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Tests\Functional\Service;

use PHPUnit\Framework\TestCase;
use SwagMigrationConnector\Tests\Functional\ContainerTrait;

class DispatchServiceTest extends TestCase
{
    use ContainerTrait;

    /**
     * @return void
     */
    public function testReadShouldBeSuccessful()
    {
        $dispatchService = $this->getContainer()->get('swag_migration_connector.service.dispatch_service');

        $dispatches = $dispatchService->getDispatches();

        static::assertCount(5, $dispatches);

        $dispatch = $dispatches[0];

        static::assertSame('9', $dispatch['id']);
        static::assertArrayHasKey('shippingCountries', $dispatch);
        static::assertArrayHasKey('paymentMethods', $dispatch);
        static::assertSame([
            [
                'countryID' => '2',
                'countryiso' => 'DE',
                'iso3' => 'DEU',
            ],
        ], $dispatch['shippingCountries']);
        static::assertSame(['2', '3', '4', '5'], $dispatch['paymentMethods']);
    }

    /**
     * @return void
     */
    public function testReadWithOffsetShouldBeSuccessful()
    {
        $dispatchService = $this->getContainer()->get('swag_migration_connector.service.dispatch_service');

        $dispatches = $dispatchService->getDispatches(1);

        static::assertCount(4, $dispatches);
    }

    /**
     * @return void
     */
    public function testReadWithLimitShouldBeSuccessful()
    {
        $dispatchService = $this->getContainer()->get('swag_migration_connector.service.dispatch_service');

        $dispatches = $dispatchService->getDispatches(0, 1);

        static::assertCount(1, $dispatches);
    }

    /**
     * @return void
     */
    public function testReadWithLimitAndOffsetShouldBeSuccessful()
    {
        $dispatchService = $this->getContainer()->get('swag_migration_connector.service.dispatch_service');

        $dispatches = $dispatchService->getDispatches(1, 1);

        static::assertCount(1, $dispatches);
    }

    /**
     * @return void
     */
    public function testReadWithOutOfBoundsOffsetShouldOfferEmptyArray()
    {
        $dispatchService = $this->getContainer()->get('swag_migration_connector.service.dispatch_service');

        $dispatches = $dispatchService->getDispatches(10);

        static::assertEmpty($dispatches);
    }
}
