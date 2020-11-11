<?php declare(strict_types=1);
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Service;

use SwagMigrationConnector\Repository\ApiRepositoryInterface;
use SwagMigrationConnector\Repository\MainVariantRelationRepository;

class MainVariantRelationService
{
    /**
     * @var MainVariantRelationRepository
     */
    private $repository;

    public function __construct(ApiRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getMainVariantRelations($offset = 0, $limit = 250)
    {
        return $this->repository->fetch($offset, $limit);
    }
}
