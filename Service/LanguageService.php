<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Service;

use Doctrine\DBAL\Connection;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Shop\Shop;

class LanguageService
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @param ModelManager $modelManager
     */
    public function __construct(ModelManager $modelManager)
    {
        $this->modelManager = $modelManager;
        $this->connection = $this->modelManager->getConnection();
    }

    /**
     * @return array
     */
    public function getLanguages()
    {
        $fetchedShopLocaleIds = array_unique($this->fetchShopLocaleIds());
        $locales = $this->fetchLocales($fetchedShopLocaleIds);

        return $this->appendAssociatedData($locales);
    }

    /**
     * @param array $locales
     *
     * @return array
     */
    private function appendAssociatedData(array $locales)
    {
        $translations = $this->fetchTranslations(array_keys($locales));

        /** @var Shop $defaultShop */
        $defaultShop = $this->modelManager->getRepository(Shop::class)->getDefault();
        // represents the main language of the migrated shop
        $defaultLocale = str_replace('_', '-', $defaultShop->getLocale()->getLocale());

        foreach ($locales as $key => &$locale) {
            if (isset($translations[$key])) {
                $locale['translations'] = $translations[$key];
            }
            $locale['locale'] = str_replace('_', '-', $locale['locale']);
            // locale of the main language in which the dataset is probably created
            $locale['_locale'] = $defaultLocale;
        }

        return array_values($locales);
    }

    /**
     * @return array
     */
    private function fetchShopLocaleIds()
    {
        $query = $this->connection->createQueryBuilder();
        $query->from('s_core_shops', 'shop');
        $query->addSelect('shop.locale_id');

        return $query->execute()->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * @param array $fetchedShopLocaleIds
     *
     * @return array
     */
    private function fetchLocales(array $fetchedShopLocaleIds)
    {
        return $this->connection->createQueryBuilder()
            ->addSelect('locale.locale as groupId, locale.id, locale.locale, locale.language')
            ->from('s_core_locales', 'locale')
            ->where('locale.id IN (:localeIds)')
            ->setParameter('localeIds', $fetchedShopLocaleIds, Connection::PARAM_STR_ARRAY)
            ->execute()
            ->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_UNIQUE);
    }

    /**
     * @param array $locales
     *
     * @return array
     */
    private function fetchTranslations(array $locales)
    {
        return $this->connection->createQueryBuilder()
            ->addSelect('snippet.name as groupId, locale.locale, snippet.value')
            ->from('s_core_snippets', 'snippet')
            ->leftJoin('snippet', 's_core_locales', 'locale', 'snippet.localeID = locale.id')
            ->where('snippet.namespace = "backend/locale/language" AND snippet.name IN (:locales)')
            ->setParameter('locales', $locales, Connection::PARAM_STR_ARRAY)
            ->execute()
            ->fetchAll(\PDO::FETCH_GROUP);
    }
}
