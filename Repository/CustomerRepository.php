<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Repository;

use Doctrine\DBAL\Connection;
use SwagMigrationConnector\Util\DefaultEntities;
use SwagMigrationConnector\Util\TotalStruct;

class CustomerRepository extends AbstractRepository
{
    /**
     * {@inheritdoc}
     */
    public function requiredForCount(array $entities)
    {
        return !\in_array(DefaultEntities::CUSTOMER, $entities);
    }

    /**
     * {@inheritdoc}
     */
    public function getTotal()
    {
        $total = (int) $this->connection->createQueryBuilder()
            ->select('COUNT(*)')
            ->from('s_user')
            ->execute()
            ->fetchColumn();

        return new TotalStruct(DefaultEntities::CUSTOMER, $total);
    }

    /**
     * {@inheritdoc}
     */
    public function fetch($offset = 0, $limit = 250)
    {
        $ids = $this->fetchIdentifiers('s_user', $offset, $limit);

        $query = $this->connection->createQueryBuilder();

        $query->from('s_user', 'customer');
        $this->addTableSelection($query, 's_user', 'customer');

        $query->leftJoin('customer', 's_user_attributes', 'attributes', 'customer.id = attributes.userID');
        $this->addTableSelection($query, 's_user_attributes', 'attributes');

        $query->leftJoin('customer', 's_core_customergroups', 'customer_group', 'customer.customergroup = customer_group.groupkey');
        $query->addSelect('customer_group.id as customerGroupId');

        $query->leftJoin('customer', 's_core_paymentmeans', 'defaultpayment', 'customer.paymentID = defaultpayment.id');
        $this->addTableSelection($query, 's_core_paymentmeans', 'defaultpayment');

        $query->leftJoin('defaultpayment', 's_core_paymentmeans_attributes', 'defaultpayment_attributes', 'defaultpayment.id = defaultpayment_attributes.paymentmeanID');
        $this->addTableSelection($query, 's_core_paymentmeans_attributes', 'defaultpayment_attributes');

        $query->leftJoin('customer', 's_core_locales', 'customerlanguage', 'customer.language = customerlanguage.id');
        $this->addTableSelection($query, 's_core_locales', 'customerlanguage');

        $query->where('customer.id IN (:ids)');
        $query->setParameter('ids', $ids, Connection::PARAM_STR_ARRAY);

        $query->addOrderBy('customer.id');

        return $query->execute()->fetchAll();
    }

    /**
     * @return array
     */
    public function fetchCustomerAdresses(array $ids)
    {
        $query = $this->connection->createQueryBuilder();

        $query->from('s_user_addresses', 'address');
        $query->addSelect('address.user_id');
        $this->addTableSelection($query, 's_user_addresses', 'address');

        $query->leftJoin('address', 's_user_addresses_attributes', 'address_attributes', 'address.id = address_attributes.address_id');
        $this->addTableSelection($query, 's_user_addresses_attributes', 'address_attributes');

        $query->leftJoin('address', 's_core_countries', 'country', 'address.country_id = country.id');
        $this->addTableSelection($query, 's_core_countries', 'country');

        $query->leftJoin('address', 's_core_countries_states', 'state', 'address.state_id = state.id');
        $this->addTableSelection($query, 's_core_countries_states', 'state');

        $query->where('address.user_id IN (:ids)');
        $query->setParameter('ids', $ids, Connection::PARAM_INT_ARRAY);

        return $query->execute()->fetchAll(\PDO::FETCH_GROUP);
    }

    /**
     * @return array
     */
    public function fetchCustomerGroupDiscounts(array $groupIds)
    {
        $query = $this->connection->createQueryBuilder();

        $query->from('s_core_customergroups_discounts', 'discount');
        $query->addSelect(['groupID']);
        $this->addTableSelection($query, 's_core_customergroups_discounts', 'discount');

        $query->where('groupID IN (:ids)');
        $query->setParameter('ids', $groupIds, Connection::PARAM_INT_ARRAY);

        return $query->execute()->fetchAll(\PDO::FETCH_GROUP);
    }

    /**
     * @return array
     */
    public function fetchPaymentData(array $ids)
    {
        $query = $this->connection->createQueryBuilder();

        $query->from('s_core_payment_data', 'paymentdata');
        $query->addSelect('paymentdata.user_id');
        $this->addTableSelection($query, 's_core_payment_data', 'paymentdata');

        $query->where('paymentdata.user_id IN (:ids)');
        $query->setParameter('ids', $ids, Connection::PARAM_INT_ARRAY);

        return $query->execute()->fetchAll(\PDO::FETCH_GROUP);
    }
}
