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
    /**
     * @return void
     */
    public function testReadOrdersShouldBeSuccessful()
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
        static::assertArrayHasKey('esd', $orders[0]['details'][0]);

        static::assertSame([
            'orderdetailsID' => '42',
            'id' => '1',
            'serialID' => '2',
            'esdID' => '1',
            'userID' => '2',
            'orderID' => '15',
            'datum' => '2012-08-30 10:15:54',
            'downloadAvailablePaymentStatus' => 'a:1:{i:0;i:12;}',
        ], $orders[0]['details'][0]['esd']);
    }

    /**
     * @return void
     */
    public function testReadOrdersWithOffsetShouldBeSuccessful()
    {
        $orderService = Shopware()->Container()->get('swag_migration_connector.service.order_service');

        $orders = $orderService->getOrders(1);

        static::assertCount(1, $orders);

        $order = $orders[0];

        static::assertSame('57', $order['id']);
        static::assertSame('20002', $order['ordernumber']);
        static::assertSame('4', $order['payment']['id']);
    }

    /**
     * @return void
     */
    public function testReadOrdersWithLimitShouldBeSuccessful()
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

    /**
     * @return void
     */
    public function testReadOrdersWithOffsetAndLimitShouldBeSuccessful()
    {
        $orderService = Shopware()->Container()->get('swag_migration_connector.service.order_service');

        $orders = $orderService->getOrders(2, 1);

        static::assertCount(0, $orders);
    }

    /**
     * @return void
     */
    public function testReadWithOutOfBoundsOffsetShouldOfferEmptyArray()
    {
        $orderService = Shopware()->Container()->get('swag_migration_connector.service.order_service');

        $orders = $orderService->getOrders(30);

        static::assertEmpty($orders);
    }
}
