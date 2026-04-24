<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Shop\Locale;
use Shopware\Models\Shop\Repository as ShopRepository;
use Shopware\Models\Shop\Shop;
use SwagMigrationConnector\Repository\OrderRepository;
use SwagMigrationConnector\Service\OrderService;

class OrderServiceTest extends TestCase
{
    public function testGetOrdersAddsDatabaseTimezoneToEveryOrder(): void
    {
        $orderService = $this->createOrderService('Europe/Berlin', [
            ['ordering.id' => '15'],
            ['ordering.id' => '57'],
        ], 2);

        $orders = $orderService->getOrders(0, 2);

        static::assertCount(2, $orders);
        static::assertSame('Europe/Berlin', $orders[0]['_timezone']);
        static::assertSame('Europe/Berlin', $orders[1]['_timezone']);
        static::assertSame('en-GB', $orders[0]['_locale']);
    }

    public function testGetOrdersOmitsDatabaseTimezoneWhenUnavailable(): void
    {
        $orderService = $this->createOrderService(null, [
            ['ordering.id' => '15'],
        ], 1);

        $orders = $orderService->getOrders(0, 1);

        static::assertCount(1, $orders);
        static::assertArrayNotHasKey('_timezone', $orders[0]);
        static::assertSame('en-GB', $orders[0]['_locale']);
    }

    /**
     * @param list<array{'ordering.id': string}> $fetchedOrders
     */
    private function createOrderService(?string $timezone, array $fetchedOrders, int $limit): OrderService
    {
        $orderIds = \array_column($fetchedOrders, 'ordering.id');
        $orderRepository = $this->getMockBuilder(OrderRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'fetch',
                'fetchOrderDetails',
                'fetchOrderEsd',
                'getEsdConfig',
                'fetchOrderDocuments',
                'getDatabaseTimezone',
            ])
            ->getMock();

        $orderRepository->expects(static::once())
            ->method('fetch')
            ->with(0, $limit)
            ->willReturn($fetchedOrders);
        $orderRepository->expects(static::once())
            ->method('fetchOrderDetails')
            ->with($orderIds)
            ->willReturn([]);
        $orderRepository->expects(static::once())
            ->method('fetchOrderEsd')
            ->with($orderIds)
            ->willReturn([]);
        $orderRepository->expects(static::once())
            ->method('getEsdConfig')
            ->willReturn(null);
        $orderRepository->expects(static::once())
            ->method('fetchOrderDocuments')
            ->with($orderIds)
            ->willReturn([]);
        $orderRepository->expects(static::once())
            ->method('getDatabaseTimezone')
            ->willReturn($timezone);

        $shopRepository = $this->getMockBuilder(ShopRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getDefault'])
            ->getMock();
        $shopRepository->expects(static::once())
            ->method('getDefault')
            ->willReturn($this->createDefaultShop());

        $modelManager = $this->getMockBuilder(ModelManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $modelManager->expects(static::once())
            ->method('getRepository')
            ->with(Shop::class)
            ->willReturn($shopRepository);

        return new OrderService(
            $orderRepository,
            $modelManager
        );
    }

    private function createDefaultShop(): Shop
    {
        $locale = new Locale();
        $locale->setLocale('en_GB');

        $shop = new Shop();
        $shop->setLocale($locale);

        return $shop;
    }
}
