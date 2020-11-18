<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Tests\Functional\Service;

use PHPUnit\Framework\TestCase;

class OrderServiceTest extends TestCase
{
    public function test_read_orders_should_be_successful()
    {
        $orderService = Shopware()->Container()->get('swag_migration_connector.service.order_service');

        $orders = $orderService->getOrders();

        static::assertCount(2, $orders);

        static::assertArrayHasKey('attributes', $orders[0]);
        static::assertArrayHasKey('_locale', $orders[0]);
        static::assertArrayHasKey('customer', $orders[0]);
        static::assertArrayHasKey('billingaddress', $orders[0]);
        static::assertArrayHasKey('shippingaddress', $orders[0]);
        static::assertArrayHasKey('payment', $orders[0]);
        static::assertArrayHasKey('details', $orders[0]);
    }

    public function test_read_orders_with_offset_should_be_successful()
    {
        $orderService = Shopware()->Container()->get('swag_migration_connector.service.order_service');

        $orders = $orderService->getOrders(1);

        static::assertCount(1, $orders);

        $order = $orders[0];

        static::assertSame('57', $order['id']);
        static::assertSame('20002', $order['ordernumber']);
        static::assertSame('4', $order['payment']['id']);
    }

    public function test_read_orders_with_limit_should_be_successful()
    {
        $orderService = Shopware()->Container()->get('swag_migration_connector.service.order_service');

        $orders = $orderService->getOrders(0, 2);

        static::assertCount(2, $orders);

        $order = $orders[0];
        static::assertSame('15', $order['id']);
        static::assertSame('19.00', $order['details'][1]['tax']['tax']);

        $order = $orders[1];
        static::assertSame('57', $order['id']);
        static::assertSame('19.00', $order['details'][1]['tax']['tax']);
    }

    public function test_read_orders_with_offset_and_limit_should_be_successful()
    {
        $orderService = Shopware()->Container()->get('swag_migration_connector.service.order_service');

        $orders = $orderService->getOrders(2, 1);

        static::assertCount(0, $orders);
    }

    public function test_read_with_out_of_bounds_offset_should_offer_empty_array()
    {
        $orderService = Shopware()->Container()->get('swag_migration_connector.service.order_service');

        $orders = $orderService->getOrders(30);

        static::assertEmpty($orders);
    }
}
