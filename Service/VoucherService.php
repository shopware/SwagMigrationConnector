<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Service;

use SwagMigrationConnector\Repository\ApiRepositoryInterface;
use SwagMigrationConnector\Repository\VoucherRepository;

class VoucherService extends AbstractApiService
{
    /**
     * @var VoucherRepository
     */
    private $voucherRepository;

    public function __construct(ApiRepositoryInterface $customerRepository)
    {
        $this->voucherRepository = $customerRepository;
    }

    /**
     * @param int $offset
     * @param int $limit
     *
     * @return array
     */
    public function getVouchers($offset = 0, $limit = 250)
    {
        $vouchers = $this->voucherRepository->fetch($offset, $limit);
        $vouchers = $this->mapData($vouchers, [], ['vouchers']);
        $ids = \array_column($vouchers, 'id');

        $resultSet = $this->assignAssociatedData($vouchers, $ids);

        return $this->cleanupResultSet($resultSet);
    }

    private function assignAssociatedData(array $vouchers, array $ids)
    {
        $fetchedCodes = $this->mapData($this->voucherRepository->fetchIndividualCodes($ids), [], ['codes']);

        foreach ($vouchers as &$voucher) {
            $promotionId = $voucher['id'];

            if (isset($fetchedCodes[$promotionId])) {
                $voucher['individualCodes'] = $fetchedCodes[$promotionId];
            }
        }

        return $vouchers;
    }
}
