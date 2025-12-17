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
     * @phpstan-ignore-next-line intentionally different return type, returns key-value config map
     *
     * @return array<string, string>
     */
    public function fetch($offset = 0, $limit = 250)
    {
        $configNames = [
            'esdKey',
            'installationDate',
        ];

        $query = $this->connection->createQueryBuilder();

        $query->select('config.name', 'config.value')
            ->from('s_core_config_elements', 'config')
            ->where('config.name IN (:configNames)')
            ->setParameter('configNames', $configNames, Connection::PARAM_STR_ARRAY);

        $rows = $query->execute()->fetchAll();

        $result = [];

        foreach ($rows as $row) {
            $value = \unserialize($row['value'], ['allowed_classes' => false]);

            $result[$row['name']] = $value;
        }

        return $result;
    }
}
