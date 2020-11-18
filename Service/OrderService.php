<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Service;

use Shopware\Components\Model\ModelManager;
use Shopware\Models\Shop\Shop;
use SwagMigrationConnector\Repository\ApiRepositoryInterface;
use SwagMigrationConnector\Repository\OrderRepository;

class OrderService extends AbstractApiService
{
    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var array
     */
    private $orderIds;

    public function __construct(ApiRepositoryInterface $orderRepository, ModelManager $modelManager)
    {
        $this->orderRepository = $orderRepository;
        $this->modelManager = $modelManager;
    }

    /**
     * @param int $offset
     * @param int $limit
     *
     * @return array
     */
    public function getOrders($offset = 0, $limit = 250)
    {
        $fetchedOrders = $this->orderRepository->fetch($offset, $limit);

        $this->orderIds = \array_column($fetchedOrders, 'ordering.id');

        $resultSet = $this->appendAssociatedData(
            $this->mapData(
                $fetchedOrders,
                [],
                ['ordering']
            )
        );

        return $this->cleanupResultSet($resultSet);
    }

    /**
     * @return array
     */
    protected function appendAssociatedData(array $orders)
    {
        $orderDetails = $this->getOrderDetails();
        $orderDocuments = $this->getOrderDocuments();

        /** @var Shop $defaultShop */
        $defaultShop = $this->modelManager->getRepository(Shop::class)->getDefault();

        // represents the main language of the migrated shop
        $locale = \str_replace('_', '-', $defaultShop->getLocale()->getLocale());

        foreach ($orders as $key => &$order) {
            $order['_locale'] = $locale;
            if (isset($orderDetails[$order['id']])) {
                $order['details'] = $orderDetails[$order['id']];
            }
            if (isset($orderDocuments[$order['id']])) {
                $order['documents'] = $orderDocuments[$order['id']];
            }
            if (isset($order['locale'])) {
                $order['locale'] = \str_replace('_', '-', $order['locale']);
            }
        }

        return $orders;
    }

    /**
     * @return array
     */
    private function getOrderDetails()
    {
        $fetchedOrderDetails = $this->orderRepository->fetchOrderDetails($this->orderIds);

        return $this->mapData($fetchedOrderDetails, [], ['detail']);
    }

    /**
     * @return array
     */
    private function getOrderDocuments()
    {
        $fetchedOrderDocuments = $this->orderRepository->fetchOrderDocuments($this->orderIds);

        return $this->mapData($fetchedOrderDocuments, [], ['document']);
    }
}
