<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Repository;

use SwagMigrationConnector\Util\TotalStruct;

interface ApiRepositoryInterface
{
    /**
     * @param int $offset
     * @param int $limit
     *
     * @return array
     */
    public function fetch($offset = 0, $limit = 250);

    /**
     * @return TotalStruct|null
     */
    public function getTotal();

    /**
     * @param array
     *
     * @return bool
     */
    public function requiredForCount(array $entities);
}
