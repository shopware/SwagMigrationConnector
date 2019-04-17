<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationAssistant\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Schema\Column;

class EnvironmentRepository
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param string $table
     *
     * @return int
     */
    public function getTableCount($table)
    {
        $querybuilder = $this->connection->createQueryBuilder();

        return (int) $querybuilder->select('COUNT(id)')
            ->from($table)
            ->execute()
            ->fetchColumn()
        ;
    }

    /**
     * @return int
     */
    public function getCategoryCount()
    {
        $querybuilder = $this->connection->createQueryBuilder();

        return (int) $querybuilder->select('COUNT(id)')
            ->from('s_categories')
            ->where('path IS NOT NULL')
            ->andWhere('parent IS NOT NULL')
            ->execute()
            ->fetchColumn()
        ;
    }

    /**
     * @return int
     */
    public function getConfiguratorOptionCount()
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $optionCount = (int) $queryBuilder->select('COUNT(id)')
            ->from('s_filter_values')
            ->execute()
            ->fetchColumn();

        $queryBuilder = $this->connection->createQueryBuilder();
        $propertyCount = (int) $queryBuilder->select('COUNT(id)')
            ->from('s_article_configurator_options')
            ->execute()
            ->fetchColumn();

        return $optionCount + $propertyCount;
    }

    /**
     * @return array
     */
    public function getShops()
    {
        $query = $this->connection->createQueryBuilder();

        $query->from('s_core_shops', 'shop');
        $query->addSelect('shop.id as identifier');
        $this->addTableSelection($query, 's_core_shops', 'shop');

        $query->leftJoin('shop', 's_core_locales', 'locale', 'shop.locale_id = locale.id');
        $this->addTableSelection($query, 's_core_locales', 'locale');

        $query->orderBy('shop.main_id');

        return $query->execute()->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_UNIQUE);
    }

    /**
     * @param QueryBuilder $query
     * @param string       $table
     * @param string       $tableAlias
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
