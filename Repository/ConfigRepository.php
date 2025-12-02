<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Repository;

use Doctrine\DBAL\Connection;

class ConfigRepository extends AbstractRepository
{
    /**
     * @return list<array<string, mixed>>
     */
    public function fetch($offset = 0, $limit = 250)
    {
        $configNames = [
            'esdKey',
            'installationDate',
        ];

        $query = $this->connection->createQueryBuilder();

        $query->from('s_core_config_elements', 'config');
        $this->addTableSelection($query, 's_core_config_elements', 'config');

        $query->where('config.name IN (:configNames)');
        $query->setParameter('configNames', $configNames, Connection::PARAM_STR_ARRAY);

        $query->setFirstResult($offset);
        $query->setMaxResults($limit);

        $rows = $query->execute()->fetchAll();

        $result = [];

        foreach ($rows as $row) {
            $name = $row['config.name'];
            $value = \unserialize($row['config.value'], ['allowed_classes' => false]);

            $result[$name] = $value;
        }

        return $result;
    }
}
