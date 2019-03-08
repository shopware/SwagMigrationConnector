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

abstract class AbstractRepository implements ApiRepositoryInterface
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param QueryBuilder $query
     * @param string       $table
     * @param string       $tableAlias
     */
    protected function addTableSelection(QueryBuilder $query, $table, $tableAlias)
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

    /**
     * @param string $table
     * @param int    $offset
     * @param int    $limit
     *
     * @return array
     */
    protected function fetchIdentifiers($table, $offset = 0, $limit = 250)
    {
        $query = $this->connection->createQueryBuilder();

        $query->select('id');
        $query->from($table);
        $query->addOrderBy('id');

        $query->setFirstResult($offset);
        $query->setMaxResults($limit);

        return $query->execute()->fetchAll(\PDO::FETCH_COLUMN);
    }
}
