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

class SeoUrlRepository extends AbstractRepository
{
    /**
     * {@inheritdoc}
     */
    public function requiredForCount(array $entities)
    {
        return !\in_array(DefaultEntities::SEO_URL, $entities);
    }

    /**
     * {@inheritdoc}
     */
    public function getTotal()
    {
        $total = (int) $this->connection->createQueryBuilder()
            ->select('COUNT(*)')
            ->from('s_core_rewrite_urls')
            ->execute()
            ->fetchColumn();

        return new TotalStruct(DefaultEntities::SEO_URL, $total);
    }

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

        if ($this->isRouterToLower()) {
            return $this->lowerSeoUrl($query->execute()->fetchAll());
        }

        return $query->execute()->fetchAll();
    }

    /**
     * @param array<int, array<string, string>> $seoUrls
     *
     * @return array<int, array<string, string>>
     */
    private function lowerSeoUrl(array $seoUrls)
    {
        foreach ($seoUrls as &$seoUrl) {
            $seoUrl['url.path'] = \strtolower($seoUrl['url.path']);
        }
        unset($seoUrl);

        return $seoUrls;
    }

    /**
     * @return bool
     */
    private function isRouterToLower()
    {
        $useUrlToLower = $this->connection->createQueryBuilder()
            ->select(['cv.value'])
            ->from('s_core_config_values', 'cv')
            ->innerJoin('cv', 's_core_config_elements', 'ce', 'cv.element_id = ce.id')
            ->where('ce.name = "routerToLower"')
            ->execute()
            ->fetchColumn();

        if (!\is_string($useUrlToLower)) {
            return true;
        }

        return (bool) unserialize($useUrlToLower);
    }
}
