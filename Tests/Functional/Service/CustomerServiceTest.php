<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Tests\Functional\Service;

use PHPUnit\Framework\TestCase;

class CustomerServiceTest extends TestCase
{
    public function test_read_customers_should_be_successful()
    {
        $customerService = Shopware()->Container()->get('swag_migration_connector.service.customer_service');

        $customers = $customerService->getCustomers();

        $this->assertCount(2, $customers);

        $customer = $customers[0];

        $this->assertSame('1', $customer['id']);
        $this->assertArrayHasKey('addresses', $customer);
        $this->assertArrayHasKey('_locale', $customer);
        $this->assertArrayHasKey('country', $customer['addresses'][0]);

        $this->assertArrayHasKey('customerGroupId', $customer);
        $this->assertArrayHasKey('customerlanguage', $customer);
    }

    public function test_read_customers_with_offset_should_be_successful()
    {
        $customerService = Shopware()->Container()->get('swag_migration_connector.service.customer_service');

        $customers = $customerService->getCustomers(1);

        $this->assertCount(1, $customers);
    }

    public function test_read_with_limit_should_be_successful()
    {
        $customerService = Shopware()->Container()->get('swag_migration_connector.service.customer_service');

        $customers = $customerService->getCustomers(0, 1);

        $this->assertCount(1, $customers);
    }

    public function test_read_with_limit_and_offset_should_be_successful()
    {
        $customerService = Shopware()->Container()->get('swag_migration_connector.service.customer_service');

        $customers = $customerService->getCustomers(1, 1);

        $this->assertCount(1, $customers);
    }

    public function test_read_with_out_of_bounds_offset_should_offer_empty_array()
    {
        $customerService = Shopware()->Container()->get('swag_migration_connector.service.customer_service');

        $customers = $customerService->getCustomers(10);

        $this->assertEmpty($customers);
    }
}
