<?php

namespace SwagMigrationApi\Service;

use SwagMigrationApi\Repository\CustomerRepository;

class CustomerService extends AbstractApiService
{
    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    /**
     * @param CustomerRepository $customerRepository
     */
    public function __construct(CustomerRepository $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    /**
     * @param int $offset
     * @param int $limit
     *
     * @return array
     */
    public function getCustomers($offset = 0, $limit = 250)
    {
        $fetchedCustomers = $this->customerRepository->fetchCustomers($offset, $limit);
        $ids = array_column($fetchedCustomers, 'customer.id');

        $customers = $this->mapData($fetchedCustomers, [], ['customer']);

        return $this->assignAssociatedData($customers, $ids);
    }

    private function assignAssociatedData(array $customers, array $ids)
    {
        $customerAddresses = $this->customerRepository->fetchCustomerAdresses($ids);
        $addresses = $this->mapData($customerAddresses, [], ['address']);

        foreach ($customers as $key => &$customer) {
            if (isset($addresses[$customer['id']])) {
                $customer['addresses'] = $addresses[$customer['id']];
            }
        }
        unset($customer);

        return $customers;
    }
}