<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Tests\Functional\Service;

use PHPUnit\Framework\TestCase;
use SwagMigrationConnector\Tests\Functional\ContainerTrait;

class CustomerServiceTest extends TestCase
{
    use ContainerTrait;

    /**
     * @return void
     */
    public function testReadCustomersShouldBeSuccessful()
    {
        $customerService = $this->getContainer()->get('swag_migration_connector.service.customer_service');

        $customers = $customerService->getCustomers();

        static::assertCount(2, $customers);

        $customer = $customers[0];

        static::assertSame('1', $customer['id']);
        static::assertArrayHasKey('addresses', $customer);
        static::assertArrayHasKey('_locale', $customer);
        static::assertArrayHasKey('country', $customer['addresses'][0]);

        static::assertArrayHasKey('customerGroupId', $customer);
        static::assertArrayHasKey('customerlanguage', $customer);
    }

    /**
     * @return void
     */
    public function testReadCustomersWithOffsetShouldBeSuccessful()
    {
        $customerService = $this->getContainer()->get('swag_migration_connector.service.customer_service');

        $customers = $customerService->getCustomers(1);

        static::assertCount(1, $customers);
    }

    /**
     * @return void
     */
    public function testReadWithLimitShouldBeSuccessful()
    {
        $customerService = $this->getContainer()->get('swag_migration_connector.service.customer_service');

        $customers = $customerService->getCustomers(0, 1);

        static::assertCount(1, $customers);
    }

    /**
     * @return void
     */
    public function testReadWithLimitAndOffsetShouldBeSuccessful()
    {
        $customerService = $this->getContainer()->get('swag_migration_connector.service.customer_service');

        $customers = $customerService->getCustomers(1, 1);

        static::assertCount(1, $customers);
    }

    /**
     * @return void
     */
    public function testReadWithOutOfBoundsOffsetShouldOfferEmptyArray()
    {
        $customerService = $this->getContainer()->get('swag_migration_connector.service.customer_service');

        $customers = $customerService->getCustomers(10);

        static::assertEmpty($customers);
    }
}
