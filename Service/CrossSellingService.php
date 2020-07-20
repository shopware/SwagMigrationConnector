<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Service;

use SwagMigrationConnector\Repository\ApiRepositoryInterface;
use SwagMigrationConnector\Repository\CrossSellingRepository;

class CrossSellingService extends AbstractApiService
{
    /**
     * @var CrossSellingRepository
     */
    private $crossSellingRepository;

    /**
     * @param ApiRepositoryInterface $customerRepository
     */
    public function __construct(
        ApiRepositoryInterface $crossSellingRepository
    ) {
        $this->crossSellingRepository = $crossSellingRepository;
    }

    /**
     * @param int $offset
     * @param int $limit
     *
     * @return array
     */
    public function getCrossSelling($offset = 0, $limit = 250)
    {
        $crossSelling = $this->crossSellingRepository->fetch($offset, $limit);

        foreach ($crossSelling as &$item) {
            $item['position'] = $offset++;
        }
        unset($item);

        return $this->cleanupResultSet($crossSelling);
    }
}
