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

class OrderRepository extends AbstractRepository
{
    const DOWNLOAD_AVAILABLE_PAYMENT_STATUS = 'downloadAvailablePaymentStatus';

    /**
     * {@inheritdoc}
     */
    public function requiredForCount(array $entities)
    {
        return !\in_array(DefaultEntities::ORDER, $entities);
    }

    /**
     * {@inheritdoc}
     */
    public function getTotal()
    {
        $total = (int) $this->connection->createQueryBuilder()
            ->select('COUNT(*)')
            ->from('s_order')
            ->where('status != -1')
            ->execute()
            ->fetchColumn();

        return new TotalStruct(DefaultEntities::ORDER, $total);
    }

    /**
     * {@inheritdoc}
     */
    public function fetch($offset = 0, $limit = 250)
    {
        $ids = $this->fetchIdentifiers('s_order', $offset, $limit, ['status != -1']);

        $query = $this->connection->createQueryBuilder();

        $query->from('s_order', 'ordering');
        $this->addTableSelection($query, 's_order', 'ordering');

        $query->leftJoin('ordering', 's_order_attributes', 'attributes', 'ordering.id = attributes.orderID');
        $this->addTableSelection($query, 's_order_attributes', 'attributes');

        /*
         * @deprecated Will be removed in version 1.0.0
         * (The left join and table selection on shippingMethod)
         */
        $query->leftJoin('ordering', 's_premium_dispatch', 'shippingMethod', 'ordering.dispatchID = shippingMethod.id');
        $this->addTableSelection($query, 's_premium_dispatch', 'shippingMethod');

        $query->leftJoin('ordering', 's_user', 'customer', 'customer.id = ordering.userID');
        $this->addTableSelection($query, 's_user', 'customer');

        $query->leftJoin('ordering', 's_core_states', 'orderstatus', 'orderstatus.group = "state" AND ordering.status = orderstatus.id');
        $this->addTableSelection($query, 's_core_states', 'orderstatus');

        $query->leftJoin('ordering', 's_core_states', 'paymentstatus', 'paymentstatus.group = "payment" AND ordering.cleared = paymentstatus.id');
        $this->addTableSelection($query, 's_core_states', 'paymentstatus');

        $query->leftJoin('ordering', 's_order_billingaddress', 'billingaddress', 'ordering.id = billingaddress.orderID');
        $this->addTableSelection($query, 's_order_billingaddress', 'billingaddress');

        $query->leftJoin('billingaddress', 's_order_billingaddress_attributes', 'billingaddress_attributes', 'billingaddress.id = billingaddress_attributes.billingID');
        $this->addTableSelection($query, 's_order_billingaddress_attributes', 'billingaddress_attributes');

        $query->leftJoin('billingaddress', 's_core_countries', 'billingaddress_country', 'billingaddress.countryID = billingaddress_country.id');
        $this->addTableSelection($query, 's_core_countries', 'billingaddress_country');

        $query->leftJoin('billingaddress', 's_core_countries_states', 'billingaddress_state', 'billingaddress.stateID = billingaddress_state.id');
        $this->addTableSelection($query, 's_core_countries_states', 'billingaddress_state');

        $query->leftJoin('ordering', 's_order_shippingaddress', 'shippingaddress', 'ordering.id = shippingaddress.orderID');
        $this->addTableSelection($query, 's_order_shippingaddress', 'shippingaddress');

        $query->leftJoin('shippingaddress', 's_order_shippingaddress_attributes', 'shippingaddress_attributes', 'shippingaddress.id = shippingaddress_attributes.shippingID');
        $this->addTableSelection($query, 's_order_shippingaddress_attributes', 'shippingaddress_attributes');

        $query->leftJoin('shippingaddress', 's_core_countries', 'shippingaddress_country', 'shippingaddress.countryID = shippingaddress_country.id');
        $this->addTableSelection($query, 's_core_countries', 'shippingaddress_country');

        $query->leftJoin('shippingaddress', 's_core_countries_states', 'shippingaddress_state', 'shippingaddress.stateID = shippingaddress_state.id');
        $this->addTableSelection($query, 's_core_countries_states', 'shippingaddress_state');

        $query->leftJoin('ordering', 's_core_paymentmeans', 'payment', 'payment.id = ordering.paymentID');
        $this->addTableSelection($query, 's_core_paymentmeans', 'payment');

        $query->leftJoin('ordering', 's_core_shops', 'languageshop', 'languageshop.id = ordering.language');
        $query->leftJoin('languageshop', 's_core_locales', 'language', 'language.id = languageshop.locale_id');
        $query->addSelect('language.locale AS \'ordering.locale\'');

        $query->where('ordering.id IN (:ids)');
        $query->setParameter('ids', $ids, Connection::PARAM_STR_ARRAY);

        $query->addOrderBy('ordering.id');

        return $query->execute()->fetchAll();
    }

    /**
     * @return array
     */
    public function fetchOrderDetails(array $orderIds)
    {
        $query = $this->connection->createQueryBuilder();

        $query->from('s_order_details', 'detail');
        $query->select('detail.orderID');
        $this->addTableSelection($query, 's_order_details', 'detail');

        $query->leftJoin('detail', 's_order_details_attributes', 'attributes', 'detail.id = attributes.detailID');
        $this->addTableSelection($query, 's_order_details_attributes', 'attributes');

        $query->leftJoin('detail', 's_core_tax', 'tax', 'tax.id = detail.taxID');
        $this->addTableSelection($query, 's_core_tax', 'tax');

        $query->where('detail.orderID IN (:ids)');
        $query->setParameter('ids', $orderIds, Connection::PARAM_INT_ARRAY);

        return $query->execute()->fetchAll(\PDO::FETCH_GROUP);
    }

    /**
     * @param array<string> $orderIds
     *
     * @return array<string>
     */
    public function fetchOrderEsd($orderIds)
    {
        $query = $this->connection->createQueryBuilder();

        $query->select('esd.orderID, esd.orderdetailsID');
        $query->from('s_order_esd', 'esd');
        $this->addTableSelection($query, 's_order_esd', 'esd');

        $query->where('esd.orderID IN (:ids)');
        $query->setParameter('ids', $orderIds, Connection::PARAM_INT_ARRAY);
        $query->setParameter('esdConfigName', self::DOWNLOAD_AVAILABLE_PAYMENT_STATUS);

        return $query->execute()->fetchAll(\PDO::FETCH_GROUP);
    }

    /**
     * @return string|null
     */
    public function getEsdConfig()
    {
        $query = $this->connection->createQueryBuilder();

        $query->select('ifnull(currentConfig.value, defaultConfig.value) as configValue');
        $query->from('s_core_config_elements', 'defaultConfig');

        $query->leftJoin('defaultConfig', 's_core_config_values', 'currentConfig', 'defaultConfig.id =  currentConfig.element_id');

        $query->where('defaultConfig.name = :esdConfigName');
        $query->setParameter('esdConfigName', self::DOWNLOAD_AVAILABLE_PAYMENT_STATUS);

        return $query->execute()->fetch(\PDO::FETCH_COLUMN);
    }

    /**
     * @param array $orderIds
     *
     * @return array
     */
    public function fetchOrderDocuments($orderIds)
    {
        $query = $this->connection->createQueryBuilder();

        $query->from('s_order_documents', 'document');
        $query->select('document.orderID');
        $this->addTableSelection($query, 's_order_documents', 'document');

        $query->leftJoin('document', 's_order_documents_attributes', 'attributes', 'document.id = attributes.documentID');
        $this->addTableSelection($query, 's_order_documents_attributes', 'attributes');

        $query->leftJoin('document', 's_core_documents', 'documenttype', 'document.type = documenttype.id');
        $this->addTableSelection($query, 's_core_documents', 'documenttype');

        $query->where('document.orderID IN (:ids)');
        $query->setParameter('ids', $orderIds, Connection::PARAM_INT_ARRAY);

        return $query->execute()->fetchAll(\PDO::FETCH_GROUP);
    }
}
