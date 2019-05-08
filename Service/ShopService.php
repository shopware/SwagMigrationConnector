<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Service;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Schema\Column;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Shop\Shop;

class ShopService extends AbstractApiService
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
    public function getShops()
    {
        $fetchedSalesChannels = $this->fetchData();
        $salesChannels = $this->mapData($fetchedSalesChannels, [], ['shop', 'locale', 'currency']);

        /** @var Shop $defaultShop */
        $defaultShop = $this->modelManager->getRepository(Shop::class)->getDefault();
        // represents the main language of the migrated shop
        $defaultLocale = str_replace('_', '-', $defaultShop->getLocale()->getLocale());

        foreach ($salesChannels as $key => &$salesChannel) {
            $salesChannel['locale'] = str_replace('_', '-', $salesChannel['locale']);
            $salesChannel['_locale'] = $defaultLocale;
            if (!empty($salesChannel['main_id'])) {
                $salesChannels[$salesChannel['main_id']]['children'][] = $salesChannel;
                unset($salesChannels[$key]);
            }
        }
        $salesChannels = array_values($salesChannels);

        return $this->cleanupResultSet($salesChannels);
    }

    /**
     * @return array
     */
    private function fetchData()
    {
        $query = $this->connection->createQueryBuilder();

        $query->from('s_core_shops', 'shop');
        $query->addSelect('shop.id as identifier');
        $this->addTableSelection($query, 's_core_shops', 'shop');

        $query->leftJoin('shop', 's_core_locales', 'locale', 'shop.locale_id = locale.id');
        $query->addSelect('locale.locale');

        $query->leftJoin('shop', 's_core_currencies', 'currency', 'shop.currency_id = currency.id');
        $query->addSelect('currency.currency');

        $query->orderBy('shop.main_id');

        return $query->execute()->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_UNIQUE);
    }

    /**
     * @param QueryBuilder $query
     * @param $table
     * @param $tableAlias
     */
    private function addTableSelection(QueryBuilder $query, $table, $tableAlias)
    {
        $columns = $this->connection->getSchemaManager()->listTableColumns($table);

        /** @var Column $column */
        foreach ($columns as $column) {
            $selection = str_replace(
                ['#tableAlias#', '#column#'],
                [$tableAlias, $column->getName()],
                '`#tableAlias#`.`#column#` as `#tableAlias#.#column#`'
            );

            $query->addSelect($selection);
        }
    }
}
