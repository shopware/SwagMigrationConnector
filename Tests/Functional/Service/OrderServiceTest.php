<?php

namespace SwagMigrationApi\Tests\Functional\Service;

class OrderServiceTest extends \PHPUnit_Framework_TestCase
{
    public function test_read_orders_should_be_successful()
    {
        $orderService = Shopware()->Container()->get('swag_migration_api.service.order_service');

        $orders = $orderService->getOrders();

        $this->assertInternalType('array', $orders);
        $this->assertCount(4, $orders);

        $this->assertArrayHasKey('attributes', $orders[0]);
        $this->assertArrayHasKey('customer', $orders[0]);
        $this->assertArrayHasKey('billingaddress', $orders[0]);
        $this->assertArrayHasKey('shippingaddress', $orders[0]);
        $this->assertArrayHasKey('payment', $orders[0]);
        $this->assertArrayHasKey('details', $orders[0]);
        $this->assertArrayHasKey('product', $orders[0]['details'][0]);
    }

    public function test_read_orders_with_offset_should_be_successful()
    {
        $orderService = Shopware()->Container()->get('swag_migration_api.service.order_service');

        $orders = $orderService->getOrders(1);

        $this->assertInternalType('array', $orders);
        $this->assertCount(3, $orders);

        $order = $orders[2];

        $this->assertSame('54', $order['id']);
        $this->assertSame('0', $order['ordernumber']);
        $this->assertSame('2', $order['payment']['id']);
    }

    public function test_read_orders_with_limit_should_be_successful()
    {
        $orderService = Shopware()->Container()->get('swag_migration_api.service.order_service');

        $orders = $orderService->getOrders(0, 2);

        $this->assertInternalType('array', $orders);
        $this->assertCount(2, $orders);

        $order = $orders[1];

        $this->assertSame('57', $order['id']);
        $this->assertSame('19.00', $order['details'][1]['tax']['tax']);
    }

    public function test_read_orders_with_offset_and_limit_should_be_successful()
    {
        $orderService = Shopware()->Container()->get('swag_migration_api.service.order_service');

        $orders = $orderService->getOrders(2, 1);

        $this->assertInternalType('array', $orders);
        $this->assertCount(1, $orders);

        $order = $orders[0];

        $this->assertSame('52', $order['id']);
    }

    public function test_read_with_out_of_bounds_offset_should_offer_empty_array()
    {
        $orderService = Shopware()->Container()->get('swag_migration_api.service.order_service');

        $orders = $orderService->getOrders(30);

        $this->assertEmpty($orders);
    }
}
