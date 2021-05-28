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

class OrderNoteRepository extends AbstractRepository
{
    /**
     * {@inheritdoc}
     */
    public function requiredForCount(array $entities)
    {
        return !\in_array(DefaultEntities::ORDER_NOTES, $entities);
    }

    /**
     * {@inheritdoc}
     */
    public function getTotal()
    {
        $total = (int) $this->connection->createQueryBuilder()
            ->select('COUNT(*)')
            ->from('s_order_notes', 'note')
            ->innerJoin('note', 's_user', 'customer', 'note.userID = customer.id')
            ->execute()->fetchColumn();

        return new TotalStruct(DefaultEntities::ORDER_NOTES, $total);
    }

    /**
     * {@inheritdoc}
     */
    public function fetch($offset = 0, $limit = 250)
    {
        $ids = $this->fetchIdentifiers('s_order_notes', $offset, $limit);
        $query = $this->connection->createQueryBuilder();

        $query->from('s_order_notes', 'note');
        $this->addTableSelection($query, 's_order_notes', 'note');

        $query->innerJoin('note', 's_user', 'customer', 'note.userID = customer.id');
        $query->addSelect('subshopID');

        $query->setParameter('ids', $ids, Connection::PARAM_STR_ARRAY);
        $query->addOrderBy('note.id');

        return $query->execute()->fetchAll();
    }
}
