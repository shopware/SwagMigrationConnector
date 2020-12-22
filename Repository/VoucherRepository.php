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

class VoucherRepository extends AbstractRepository
{
    /**
     * {@inheritdoc}
     */
    public function requiredForCount(array $entities)
    {
        return !\in_array(DefaultEntities::PROMOTION, $entities);
    }

    /**
     * {@inheritdoc}
     */
    public function getTotal()
    {
        $total = (int) $this->connection->createQueryBuilder()
            ->select('COUNT(*)')
            ->from('s_emarketing_vouchers')
            ->execute()
            ->fetchColumn();

        return new TotalStruct(DefaultEntities::PROMOTION, $total);
    }

    /**
     * {@inheritdoc}
     */
    public function fetch($offset = 0, $limit = 250)
    {
        $ids = $this->fetchIdentifiers('s_emarketing_vouchers', $offset, $limit);
        $query = $this->connection->createQueryBuilder();

        $query->from('s_emarketing_vouchers', 'vouchers');
        $this->addTableSelection($query, 's_emarketing_vouchers', 'vouchers');

        $query->where('vouchers.id IN (:ids)');
        $query->setParameter('ids', $ids, Connection::PARAM_STR_ARRAY);

        return $query->execute()->fetchAll();
    }

    /**
     * @return array
     */
    public function fetchIndividualCodes(array $ids)
    {
        $query = $this->connection->createQueryBuilder();

        $query->from('s_emarketing_voucher_codes', 'codes');
        $query->addSelect('codes.voucherID');
        $this->addTableSelection($query, 's_emarketing_voucher_codes', 'codes');

        $query->leftJoin('codes', 's_user', 'user', 'codes.userID = user.id');
        $query->addSelect('user.firstname AS `codes.firstname`, user.lastname AS `codes.lastname`');

        $query->where('codes.voucherID IN (:ids)');
        $query->setParameter('ids', $ids, Connection::PARAM_STR_ARRAY);

        return $query->execute()->fetchAll(\PDO::FETCH_GROUP);
    }
}
