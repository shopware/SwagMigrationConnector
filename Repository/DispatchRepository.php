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

class DispatchRepository extends AbstractRepository
{
    /**
     * {@inheritdoc}
     */
    public function requiredForCount(array $entities)
    {
        return !\in_array(DefaultEntities::SHIPPING_METHOD, $entities);
    }

    /**
     * {@inheritdoc}
     */
    public function getTotal()
    {
        $total = (int) $this->connection->createQueryBuilder()
            ->select('COUNT(*)')
            ->from('s_premium_dispatch')
            ->execute()
            ->fetchColumn();

        return new TotalStruct(DefaultEntities::SHIPPING_METHOD, $total);
    }

    /**
     * {@inheritdoc}
     */
    public function fetch($offset = 0, $limit = 250)
    {
        $ids = $this->fetchIdentifiers('s_premium_dispatch', $offset, $limit);

        $query = $this->connection->createQueryBuilder();

        $query->from('s_premium_dispatch', 'dispatch');
        $this->addTableSelection($query, 's_premium_dispatch', 'dispatch');

        $query->leftJoin('dispatch', 's_core_shops', 'shop', 'dispatch.multishopID = shop.id');
        $this->addTableSelection($query, 's_core_shops', 'shop');

        $query->leftJoin('dispatch', 's_core_customergroups', 'customerGroup', 'dispatch.customergroupID = customerGroup.id');
        $this->addTableSelection($query, 's_core_customergroups', 'customerGroup');

        $query->leftJoin('dispatch', 's_core_tax', 'tax', 'dispatch.tax_calculation = tax.id');
        $this->addTableSelection($query, 's_core_tax', 'tax');

        $query->where('dispatch.id IN (:ids)');
        $query->setParameter('ids', $ids, Connection::PARAM_STR_ARRAY);

        $query->addOrderBy('dispatch.id');

        return $query->execute()->fetchAll();
    }

    /**
     * @return array
     */
    public function fetchShippingCosts(array $shippingMethodIds)
    {
        $query = $this->connection->createQueryBuilder();

        $query->from('s_premium_shippingcosts', 'shippingcosts');
        $query->addSelect('shippingcosts.dispatchID as dispatchId');
        $this->addTableSelection($query, 's_premium_shippingcosts', 'shippingcosts');

        $query->leftJoin('shippingcosts', 's_core_currencies', 'currency', 'currency.standard = 1');
        $query->addSelect('currency.currency as currencyShortName');

        $query->where('shippingcosts.dispatchID IN (:ids)');
        $query->setParameter('ids', $shippingMethodIds, Connection::PARAM_STR_ARRAY);

        $query->orderBy('shippingcosts.from');

        return $query->execute()->fetchAll(\PDO::FETCH_GROUP);
    }

    /**
     * @return array
     */
    public function fetchShippingCountries(array $shippingMethodIds)
    {
        $query = $this->connection->createQueryBuilder();

        $query->from('s_premium_dispatch_countries', 'shippingcountries');
        $query->addSelect('shippingcountries.dispatchID, shippingcountries.countryID');

        $query->innerJoin('shippingcountries', 's_core_countries', 'country', 'country.id = shippingcountries.countryID');
        $query->addSelect('country.countryiso', 'country.iso3');

        $query->where('shippingcountries.dispatchID IN (:ids)');
        $query->setParameter('ids', $shippingMethodIds, Connection::PARAM_STR_ARRAY);
        $query->orderBy('shippingcountries.dispatchID, shippingcountries.countryID');

        return $query->execute()->fetchAll(\PDO::FETCH_GROUP);
    }

    /**
     * @return array
     */
    public function fetchPaymentMethods(array $shippingMethodIds)
    {
        $query = $this->connection->createQueryBuilder();

        $query->from('s_premium_dispatch_paymentmeans', 'paymentMethods');
        $query->addSelect('paymentMethods.dispatchID, paymentMethods.paymentID');

        $query->where('paymentMethods.dispatchID IN (:ids)');
        $query->setParameter('ids', $shippingMethodIds, Connection::PARAM_STR_ARRAY);
        $query->orderBy('paymentMethods.dispatchID, paymentMethods.paymentID');

        return $query->execute()->fetchAll(\PDO::FETCH_GROUP);
    }

    /**
     * @return array
     */
    public function fetchExcludedCategories(array $shippingMethodIds)
    {
        $query = $this->connection->createQueryBuilder();

        $query->from('s_premium_dispatch_categories', 'categories');
        $query->addSelect('categories.dispatchID, categories.categoryID');

        $query->where('categories.dispatchID IN (:ids)');
        $query->setParameter('ids', $shippingMethodIds, Connection::PARAM_STR_ARRAY);
        $query->orderBy('categories.dispatchID, categories.categoryID');

        return $query->execute()->fetchAll(\PDO::FETCH_GROUP);
    }
}
