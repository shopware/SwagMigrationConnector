<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationAssistant\Repository;

use Doctrine\DBAL\Connection;

class AttributeRepository
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
     * @return array
     */
    public function getAttributeConfiguration($table)
    {
        return $this->connection->createQueryBuilder()
            ->select('config.column_name, config.*')
            ->from('s_attribute_configuration', 'config')
            ->where('config.table_name = :table')
            ->setParameter('table', $table)
            ->execute()
            ->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_UNIQUE)
        ;
    }

    /**
     * @param $table
     *
     * @return array
     */
    public function getAttibuteConfigurationTranslations($table)
    {
        $sql = <<<SQL
SELECT s.*, l.locale
FROM s_core_snippets s
LEFT JOIN s_core_locales l ON s.localeID = l.id
WHERE namespace = 'backend/attribute_columns'
AND name LIKE :table
SQL;

        return $this->connection->executeQuery(
            $sql,
            [
                'pos' => $table,
                'table' => $table . '%'
            ]
        )->fetchAll();
    }
}
