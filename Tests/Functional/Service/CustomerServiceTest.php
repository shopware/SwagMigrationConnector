<?php

namespace SwagMigrationApi\Tests\Functional\Service;

class CustomerServiceTest extends \PHPUnit_Framework_TestCase
{
    public function test_read_customers_should_be_successful()
    {
        $customerService = Shopware()->Container()->get('swag_migration_api.service.customer_service');

        $customers = $customerService->getCustomers();

        $this->assertInternalType('array', $customers);
        $this->assertCount(2, $customers);

        $customer = $customers[0];

        $this->assertInternalType('array', $customer);
        $this->assertSame('1', $customer['id']);
        $this->assertArrayHasKey('addresses', $customer);
        $this->assertArrayHasKey('country', $customer['addresses'][0]);

        $this->assertArrayHasKey('group', $customer);
        $this->assertArrayHasKey('customerlanguage', $customer);
        $this->assertArrayHasKey('shop', $customer);
    }

    public function test_read_customers_with_offset_should_be_successful()
    {
        $customerService = Shopware()->Container()->get('swag_migration_api.service.customer_service');

        $customers = $customerService->getCustomers(1);

        $this->assertInternalType('array', $customers);
        $this->assertCount(1, $customers);
    }

    public function test_read_with_limit_should_be_successful()
    {
        $customerService = Shopware()->Container()->get('swag_migration_api.service.customer_service');

        $customers = $customerService->getCustomers(0, 1);

        $this->assertInternalType('array', $customers);
        $this->assertCount(1, $customers);
    }

    public function test_read_with_limit_and_offset_should_be_successful()
    {
        $customerService = Shopware()->Container()->get('swag_migration_api.service.customer_service');

        $customers = $customerService->getCustomers(1, 1);

        $this->assertInternalType('array', $customers);
        $this->assertCount(1, $customers);
    }

    public function test_read_with_out_of_bounds_offset_should_offer_empty_array()
    {
        $customerService = Shopware()->Container()->get('swag_migration_api.service.customer_service');

        $customers = $customerService->getCustomers(10);

        $this->assertEmpty($customers);
    }
}
