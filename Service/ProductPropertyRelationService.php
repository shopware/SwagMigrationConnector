<?php

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
