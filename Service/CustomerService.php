<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationApi\Service;

use Shopware\Components\Model\ModelManager;
use Shopware\Models\Shop\Shop;
use SwagMigrationApi\Repository\CustomerRepository;

class CustomerService extends AbstractApiService
{
    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @param CustomerRepository $customerRepository
     * @param ModelManager       $modelManager
     */
    public function __construct(CustomerRepository $customerRepository, ModelManager $modelManager)
    {
        $this->customerRepository = $customerRepository;
        $this->modelManager = $modelManager;
    }

    /**
     * @param int $offset
     * @param int $limit
     *
     * @return array
     */
    public function getCustomers($offset = 0, $limit = 250)
    {
        $fetchedCustomers = $this->customerRepository->fetch($offset, $limit);
        $ids = array_column($fetchedCustomers, 'customer.id');

        $customers = $this->mapData($fetchedCustomers, [], ['customer']);

        $resultSet = $this->assignAssociatedData($customers, $ids);

        return $this->cleanupResultSet($resultSet);
    }

    private function assignAssociatedData(array $customers, array $ids)
    {
        $customerAddresses = $this->customerRepository->fetchCustomerAdresses($ids);
        $addresses = $this->mapData($customerAddresses, [], ['address']);

        $fetchedPaymentData = $this->customerRepository->fetchPaymentData($ids);
        $paymentData = $this->mapData($fetchedPaymentData, [], ['paymentdata']);

        /** @var Shop $defaultShop */
        $defaultShop = $this->modelManager->getRepository(Shop::class)->getDefault();

        // represents the main language of the migrated shop
        $locale = $defaultShop->getLocale()->getLocale();

        foreach ($customers as $key => &$customer) {
            $customer['_locale'] = $locale;
            if (isset($addresses[$customer['id']])) {
                $customer['addresses'] = $addresses[$customer['id']];
            }
            if (isset($paymentData[$customer['id']])) {
                $customer['paymentdata'] = $paymentData[$customer['id']];
            }
        }
        unset($customer);

        return $customers;
    }
}
