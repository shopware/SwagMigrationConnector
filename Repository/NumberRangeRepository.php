<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Repository;

use SwagMigrationConnector\Util\DefaultEntities;
use SwagMigrationConnector\Util\TotalStruct;

class NumberRangeRepository extends AbstractRepository
{
    /**
     * {@inheritDoc}
     */
    public function requiredForCount(array $entities)
    {
        return !in_array(DefaultEntities::NUMBER_RANGE, $entities);
    }

    /**
     * {@inheritdoc}
     */
    public function getTotal()
    {
        $total = (int) $this->connection->createQueryBuilder()
            ->select('COUNT(*)')
            ->from('s_order_number')
            ->execute()
            ->fetchColumn();

        return new TotalStruct(DefaultEntities::NUMBER_RANGE,$total);
    }

    /**
     * @return array
     */
    public function fetch($offset = 0, $limit = 250)
    {
        return $this->connection->createQueryBuilder()
            ->select('*')
            ->from('s_order_number')
            ->execute()
            ->fetchAll();
    }

    /**
     * @return mixed
     */
    public function fetchPrefix()
    {
        return $this->connection->createQueryBuilder()
            ->select('value')
            ->from('s_core_config_elements')
            ->where('name = "backendautoordernumberprefix"')
            ->execute()
            ->fetch(\PDO::FETCH_COLUMN);
    }
}
