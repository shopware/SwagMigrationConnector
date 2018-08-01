<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationApi\Service;

use SwagMigrationApi\Repository\OrderRepository;

class OrderService extends AbstractApiService
{
    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var array
     */
    private $orderIds;

    /**
     * @param OrderRepository $orderRepository
     */
    public function __construct(OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param int $offset
     * @param int $limit
     *
     * @return array
     */
    public function getOrders($offset = 0, $limit = 250)
    {
        $fetchedOrders = $this->orderRepository->fetchOrders($offset, $limit);

        $this->orderIds = array_column($fetchedOrders, 'ordering.id');

        return $this->appendAssociatedData(
            $this->mapData(
                $fetchedOrders, [], ['ordering']
            )
        );
    }

    /**
     * @param array $orders
     *
     * @return array
     */
    protected function appendAssociatedData(array $orders)
    {
        $orderDetails = $this->getOrderDetails();
        $orderDocuments = $this->getOrderDocuments();

        foreach ($orders as $key => &$order) {
            if (isset($orderDetails[$order['id']])) {
                $order['details'] = $orderDetails[$order['id']];
            }
            if (isset($orderDocuments[$order['id']])) {
                $order['documents'] = $orderDocuments[$order['id']];
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
