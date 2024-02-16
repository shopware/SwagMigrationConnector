<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Schema\Column;
use SwagMigrationConnector\Util\TotalStruct;

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

    /**
     * @return TotalStruct|null
     */
    public function getTotal()
    {
        return null;
    }

    /**
     * @return bool
     */
    public function requiredForCount(array $entities)
    {
        return false;
    }

    /**
     * @param string $table
     * @param string $tableAlias
     */
    final protected function addTableSelection(QueryBuilder $query, $table, $tableAlias)
    {
        $columns = $this->connection->getSchemaManager()->listTableColumns($table);

        /** @var Column $column */
        foreach ($columns as $column) {
            $selection = \str_replace(
                ['#tableAlias#', '#column#'],
                [$tableAlias, $column->getName()],
                '`#tableAlias#`.`#column#` as `#tableAlias#.#column#`'
            );

            $query->addSelect($selection);
        }
    }

    /**
     * @param string             $table
     * @param int                $offset
     * @param int                $limit
     * @param array<int, string> $where
     *
     * @return array
     */
    final protected function fetchIdentifiers($table, $offset = 0, $limit = 250, $where = [])
    {
        $query = $this->connection->createQueryBuilder();

        $query->select('id');
        $query->from($table);
        $query->addOrderBy('id');

        foreach ($where as $clause) {
            $query->andWhere($clause);
        }

        $query->setFirstResult($offset);
        $query->setMaxResults($limit);

        return $query->execute()->fetchAll(\PDO::FETCH_COLUMN);
    }
}
