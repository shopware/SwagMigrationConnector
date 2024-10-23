<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Repository;

use Doctrine\DBAL\Connection;
use SwagMigrationConnector\Util\DefaultEntities;
use SwagMigrationConnector\Util\TotalStruct;

class NumberRangeRepository extends AbstractRepository
{
    /**
     * {@inheritdoc}
     */
    public function requiredForCount(array $entities)
    {
        return !\in_array(DefaultEntities::NUMBER_RANGE, $entities);
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

        return new TotalStruct(DefaultEntities::NUMBER_RANGE, $total);
    }

    /**
     * @return list<array{id: string, number: string, name: string, desc: string}>
     */
    public function fetch($offset = 0, $limit = 250)
    {
        $ids = $this->fetchIdentifiers('s_order_number', $offset, $limit);

        $query = $this->connection->createQueryBuilder();
        $query->select('*');
        $query->from('s_order_number');
        $query->where('id IN (:ids)');
        $query->setParameter('ids', $ids, Connection::PARAM_STR_ARRAY);
        $query->orderBy('id');

        return $query->execute()->fetchAll();
    }

    /**
     * Configuration value is only valid for product order numbers
     *
     * @return string
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
