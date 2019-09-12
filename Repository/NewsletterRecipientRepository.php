<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Repository;

use Doctrine\DBAL\Connection;

class NewsletterRecipientRepository extends AbstractRepository
{
    /**
     * {@inheritdoc}
     */
    public function fetch($offset = 0, $limit = 250)
    {
        $ids = $this->fetchIdentifiers('s_campaigns_mailaddresses', $offset, $limit);

        $query = $this->connection->createQueryBuilder();

        $query->select('addresses.id, newsletter.*, addresses.customer');

        $query->from('s_campaigns_mailaddresses', 'addresses');
        $query->leftJoin('addresses', 's_campaigns_maildata', 'newsletter', 'addresses.email = newsletter.email');

        $query->where('newsletter.id IN (:ids)');
        $query->setParameter('ids', $ids, Connection::PARAM_STR_ARRAY);
        $query->addOrderBy('newsletter.id');

        return $query->execute()->fetchAll();
    }

    /**
     * @return array
     */
    public function getDefaultShopAndLocaleByGroupId()
    {
        $query = $this->connection->createQueryBuilder();

        $query->addSelect('config.value as groupID');
        $query->addSelect('shop.id as shopId');
        $query->addSelect('locale.locale as locale');

        $query->from('s_core_config_elements', 'config');
        $query->innerJoin('config', 's_core_shops', 'shop', 'shop.default = true');
        $query->innerJoin('shop', 's_core_locales', 'locale', 'locale.id = shop.locale_id');
        $query->where('config.name = \'newsletterdefaultgroup\'');

        $shops = $query->execute()->fetchAll();

        return $this->getGroupedResult($shops);
    }

    /**
     * @return array
     */
    public function getShopsAndLocalesByGroupId()
    {
        $query = $this->connection->createQueryBuilder();

        $query->addSelect('config_values.value as groupID');
        $query->addSelect('shop.id as shopId');
        $query->addSelect('shop.main_id as mainId');
        $query->addSelect('locale.locale as locale');

        $query->from('s_core_config_elements', 'config');
        $query->innerJoin('config', 's_core_config_values', 'config_values', 'config.id = config_values.element_id');
        $query->innerJoin('config', 's_core_shops', 'shop', 'shop.id = config_values.shop_id');
        $query->innerJoin('shop', 's_core_locales', 'locale', 'locale.id = shop.locale_id');
        $query->where('config.name = \'newsletterdefaultgroup\'');

        $shops = $query->execute()->fetchAll();

        return $this->getGroupedResult($shops);
    }

    /**
     * @param array $ids
     *
     * @return array
     */
    public function getShopsAndLocalesByCustomer(array $ids)
    {
        $query = $this->connection->createQueryBuilder();

        $query->addSelect('addresses.id as addressId');
        $query->addSelect('shop.id as shopId');
        $query->addSelect('shop.main_id as mainId');
        $query->addSelect('locale.locale as locale');

        $query->from('s_campaigns_mailaddresses', 'addresses');
        $query->innerJoin('addresses', 's_user', 'users', 'users.email = addresses.email and users.accountmode = 0');
        $query->innerJoin('users', 's_core_shops', 'shop', 'shop.id = users.subshopID');
        $query->innerJoin('users', 's_core_shops', 'language', 'language.id = users.language');
        $query->innerJoin('language', 's_core_locales', 'locale', 'locale.id = language.locale_id');
        $query->where('addresses.customer = 1');
        $query->andWhere('addresses.id IN (:ids)');
        $query->setParameter('ids', $ids, Connection::PARAM_STR_ARRAY);

        return $query->execute()->fetchAll(\PDO::FETCH_GROUP);
    }

    /**
     * @param array $shops
     *
     * @return array
     */
    private function getGroupedResult(array $shops)
    {
        $resultSet = [];

        foreach ($shops as $shop) {
            $groupId = unserialize($shop['groupID'], ['allowed_classes' => false]);
            if (!isset($resultSet[$groupId])) {
                $resultSet[$groupId] = [];
            }
            $resultSet[$groupId][] = $shop;
        }
        return $resultSet;
    }
}
