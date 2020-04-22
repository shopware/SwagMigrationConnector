<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Service;

use SwagMigrationConnector\Repository\ApiRepositoryInterface;
use SwagMigrationConnector\Repository\ProductPropertyRelationRepository;

class ProductPropertyRelationService extends AbstractApiService
{
    /**
     * @var ProductPropertyRelationRepository
     */
    private $repository;

    public function __construct(ApiRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getProductPropertyRelations($offset = 0, $limit = 250)
    {
        return $this->mapData($this->repository->fetch($offset, $limit), [], ['identifier', 'type', 'filter', 'name', 'value', 'productId']);
    }
}
