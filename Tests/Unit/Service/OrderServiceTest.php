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
use Shopware\Models\Shop\Shop;
use SwagMigrationConnector\Repository\OrderRepository;
use SwagMigrationConnector\Service\OrderService;

require_once \dirname(__DIR__, 3) . '/Repository/ApiRepositoryInterface.php';
require_once \dirname(__DIR__, 3) . '/Repository/AbstractRepository.php';
require_once \dirname(__DIR__, 3) . '/Repository/OrderRepository.php';
require_once \dirname(__DIR__, 3) . '/Service/AbstractApiService.php';
require_once \dirname(__DIR__, 3) . '/Service/OrderService.php';

class OrderServiceTest extends TestCase
{
    /**
     * @return void
     */
    public function testGetOrdersAddsDatabaseTimezoneToEveryOrder()
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

    /**
     * @return void
     */
    public function testGetOrdersOmitsDatabaseTimezoneWhenUnavailable()
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
     * @param string|null $timezone
     * @param array       $fetchedOrders
     * @param int         $limit
     *
     * @return OrderService
     */
    private function createOrderService($timezone, array $fetchedOrders, $limit)
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

        $shopRepository = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['getDefault'])
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

    /**
     * @return Shop
     */
    private function createDefaultShop()
    {
        $locale = new Locale();
        $locale->setLocale('en_GB');

        $shop = new Shop();
        $shop->setLocale($locale);

        return $shop;
    }
}
