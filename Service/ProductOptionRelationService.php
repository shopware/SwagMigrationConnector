<?php declare(strict_types=1);
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Service;

use SwagMigrationConnector\Repository\ApiRepositoryInterface;
use SwagMigrationConnector\Repository\ProductOptionRelationRepository;

class ProductOptionRelationService extends AbstractApiService
{
    /**
     * @var ProductOptionRelationRepository
     */
    private $repository;

    public function __construct(ApiRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getProductOptionRelations($offset = 0, $limit = 250)
    {
        return $this->mapData($this->repository->fetch($offset, $limit), [], ['identifier', 'type', 'configurator', 'option', 'productId']);
    }
}
