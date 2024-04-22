<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

abstract class AbstractRepository implements ApiRepositoryInterface
{
    /**
     * @var Connection
     */
    protected $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function getTotal()
    {
        return null;
    }

    public function requiredForCount(array $entities)
    {
        return false;
    }

    /**
     * @param string $table
     * @param string $tableAlias
     *
     * @return void
     */
    final protected function addTableSelection(QueryBuilder $query, $table, $tableAlias)
    {
        foreach ($this->connection->getSchemaManager()->listTableColumns($table) as $column) {
            $selection = \str_replace(
                ['#tableAlias#', '#column#'],
                [$tableAlias, $column->getName()],
                '`#tableAlias#`.`#column#` as `#tableAlias#.#column#`'
            );

            $query->addSelect($selection);
        }
    }

    /**
     * @param string       $table
     * @param int          $offset
     * @param int          $limit
     * @param list<string> $orderBy
     * @param list<string> $where
     *
     * @return list<array<string, mixed>>
     */
    final protected function fetchIdentifiers($table, $offset = 0, $limit = 250, $orderBy = [], $where = [])
    {
        $query = $this->connection->createQueryBuilder();

        $query->select('id');
        $query->from($table);

        if (empty($orderBy)) {
            $orderBy = ['id'];
        }

        foreach ($orderBy as $order) {
            $query->addOrderBy($order);
        }

        foreach ($where as $clause) {
            $query->andWhere($clause);
        }

        $query->setFirstResult($offset);
        $query->setMaxResults($limit);

        return $query->execute()->fetchAll(\PDO::FETCH_COLUMN);
    }
}
