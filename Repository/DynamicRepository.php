<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Repository;

use Doctrine\DBAL\Connection;
use SwagMigrationConnector\Exception\UnknownTableException;

/**
 * @deprecated - Will be removed in 3.0.0 as it is not used anymore
 */
class DynamicRepository
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param string               $table
     * @param int                  $offset
     * @param int                  $limit
     * @param array<string, mixed> $filter
     *
     * @return list<array<string, mixed>>
     */
    public function fetch($table, $offset = 0, $limit = 250, array $filter = [])
    {
        $schemaManager = $this->connection->getSchemaManager();

        if (!$schemaManager->tablesExist([$table])) {
            throw new UnknownTableException('The table: ' . $table . ' could not be found.');
        }
        $query = $this->connection->createQueryBuilder();

        $query->addSelect('*');
        $query->from($table);

        if (!empty($filter)) {
            foreach ($filter as $property => $value) {
                $query->andWhere($property . ' = :value');
                $query->setParameter('value', $value);
            }
        }

        $query->setFirstResult($offset);
        $query->setMaxResults($limit);

        return $query->execute()->fetchAll();
    }
}
