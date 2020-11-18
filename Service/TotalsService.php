<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Service;

use Doctrine\DBAL\Connection;
use SwagMigrationConnector\Repository\ApiRepositoryInterface;
use SwagMigrationConnector\Util\RepositoryRegistry;

class TotalsService
{
    /**
     * @var RepositoryRegistry
     */
    private $repositoryRegistry;

    /**
     * @var Connection
     */
    private $connection;

    public function __construct(RepositoryRegistry $repositoryRegistry, Connection $connection)
    {
        $this->repositoryRegistry = $repositoryRegistry;
        $this->connection = $connection;
    }

    /**
     * @return array
     */
    public function fetchTotals(array $countInfos = [])
    {
        $totals = [];
        $exceptions = [];
        $entities = [];

        if (!empty($countInfos)) {
            $entities = \array_column($countInfos, 'entity');
        }
        /**
         * @var ApiRepositoryInterface[]
         */
        $repos = $this->repositoryRegistry->getRepositories($entities);
        foreach ($repos as $repo) {
            $total = $repo->getTotal();

            if ($total !== null) {
                $totals[$total->getEntityName()] = $total->getTotal();
            }
        }

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
