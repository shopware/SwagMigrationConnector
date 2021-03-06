<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Util;

use SwagMigrationConnector\Repository\ApiRepositoryInterface;

/**
 * Class holds all repositories that are able to deliver
 * a count of their entities.
 */
class RepositoryRegistry
{
    /**
     * @var ApiRepositoryInterface[]
     */
    private $repositories;

    public function __construct($repositories)
    {
        if ($repositories instanceof \IteratorAggregate) {
            $repositories = \iterator_to_array($repositories, true);
        }

        $this->repositories = $repositories;
    }

    /**
     * @return ApiRepositoryInterface[]
     */
    public function getRepositories(array $entities)
    {
        $repos = [];
        foreach ($this->repositories as $repository) {
            if ($repository->requiredForCount($entities)) {
                $repos[] = $repository;
            }
        }

        return $repos;
    }
}
