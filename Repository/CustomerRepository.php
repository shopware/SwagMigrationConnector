<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationApi\Repository;

use Doctrine\DBAL\Connection;

class CustomerRepository extends AbstractRepository
{
    /**
     * @param int $offset
     * @param int $limit
     *
     * @return array
     */
    public function fetchCustomers($offset = 0, $limit = 250)
    {
        $query = $this->getConnection()->createQueryBuilder();

        $query->from('s_user', 'customer');
        $this->addTableSelection($query, 's_user', 'customer');

        $query->leftJoin('customer', 's_user_attributes', 'attributes', 'customer.id = attributes.userID');
        $this->addTableSelection($query, 's_user_attributes', 'attributes');

        $query->setFirstResult($offset);
        $query->setMaxResults($limit);

        return $query->execute()->fetchAll();
    }

    /**
     * @param array $ids
     *
     * @return array
     */
    public function fetchCustomerAdresses(array $ids)
    {
        $query = $this->getConnection()->createQueryBuilder();

        $query->from('s_user_addresses', 'address');
        $query->addSelect('address.user_id');
        $this->addTableSelection($query, 's_user_addresses', 'address');

        $query->leftJoin('address', 's_user_addresses_attributes', 'address_attributes', 'address.id = address_attributes.address_id');
        $this->addTableSelection($query, 's_user_addresses_attributes', 'address_attributes');

        $query->where('address.user_id IN (:ids)');
        $query->setParameter('ids', $ids, Connection::PARAM_INT_ARRAY);

        return $query->execute()->fetchAll(\PDO::FETCH_GROUP);
    }
}
