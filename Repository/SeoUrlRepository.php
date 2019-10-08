<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Repository;

use Doctrine\DBAL\Connection;

class SeoUrlRepository extends AbstractRepository
{
    /**
     * {@inheritdoc}
     */
    public function fetch($offset = 0, $limit = 250)
    {
        $ids = $this->fetchIdentifiers('s_core_rewrite_urls', $offset, $limit);

        $query = $this->connection->createQueryBuilder();

        $query->from('s_core_rewrite_urls', 'url');
        $this->addTableSelection($query, 's_core_rewrite_urls', 'url');

        $query->leftJoin('url', 's_core_shops', 'shop', 'shop.id = url.subshopID');
        $query->leftJoin('shop', 's_core_locales', 'locale', 'shop.locale_id = locale.id');
        $query->addSelect('locale.locale as _locale');

        $query->where('url.id IN (:ids)');
        $query->setParameter('ids', $ids, Connection::PARAM_STR_ARRAY);

        return $query->execute()->fetchAll();
    }
}
