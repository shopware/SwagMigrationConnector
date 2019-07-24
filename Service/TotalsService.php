<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Service;

use Doctrine\DBAL\Connection;

class TotalsService
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
     * @param array $countInfos
     *
     * @return array
     */
    public function fetchTotals(array $countInfos)
    {
        $totals = [];
        $exceptions = [];

        foreach ($countInfos as $entityInfo) {
            $entity = $entityInfo['entity'];
            $queryRules = $entityInfo['queryRules'];

            $total = 0;
            foreach ($queryRules as $queryRule) {
                try {
                    $query = $this->connection->createQueryBuilder();
                    $query = $query->select('COUNT(*)')->from($queryRule['table']);

                    if (isset($queryRule['conditions'])) {
                        $query->where($queryRule['condition']);
                    }
                    $total += (int) $query->execute()->fetchColumn();
                } catch (\Exception $exception) {
                    $exceptions[] = [
                        'code' => $exception->getCode(),
                        'message' => $exception->getMessage(),
                        'entity' => $entity,
                        'table' => $queryRule['table'],
                        'condition' => isset($queryRule['condition']) ? $queryRule['condition'] : null,
                    ];
                }
            }

            $totals[$entity] = $total;
        }

        return [
            'totals' => $totals,
            'exceptions' => $exceptions,
        ];
    }
}
