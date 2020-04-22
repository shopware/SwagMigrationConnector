<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Util;

class TotalStruct
{
    /**
     * @var string
     */
    private $entityName;

    /**
     * @var int
     */
    private $total;

    /**
     * @param string $entityName
     * @param int    $total
     */
    public function __construct($entityName, $total)
    {
        $this->entityName = $entityName;
        $this->total = $total;
    }

    /**
     * @return string
     */
    public function getEntityName()
    {
        return $this->entityName;
    }

    /**
     * @return int
     */
    public function getTotal()
    {
        return $this->total;
    }
}
