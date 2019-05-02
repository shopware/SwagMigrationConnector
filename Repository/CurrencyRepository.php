<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationAssistant\Repository;

class CurrencyRepository extends AbstractRepository
{
    /**
     * @param int $offset
     * @param int $limit
     *
     * @return array
     */
    public function fetch($offset = 0, $limit = 250)
    {
        $query = $this->connection->createQueryBuilder();
        $query->from('s_core_currencies', 'currency');
        $this->addTableSelection($query, 's_core_currencies', 'currency');

        $query->setFirstResult($offset);
        $query->setMaxResults($limit);

        return $query->execute()->fetchAll();
    }
}
