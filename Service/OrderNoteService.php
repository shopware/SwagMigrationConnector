<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Service;

use SwagMigrationConnector\Repository\ApiRepositoryInterface;
use SwagMigrationConnector\Repository\CrossSellingRepository;

class OrderNoteService extends AbstractApiService
{
    /**
     * @var CrossSellingRepository
     */
    private $orderNoteRepository;

    public function __construct(
        ApiRepositoryInterface $orderNoteRepository
    ) {
        $this->orderNoteRepository = $orderNoteRepository;
    }

    /**
     * @param int $offset
     * @param int $limit
     *
     * @return array
     */
    public function getOrderNotes($offset = 0, $limit = 250)
    {
        $orderNotes = $this->orderNoteRepository->fetch($offset, $limit);
        $orderNotes = $this->mapData($orderNotes, [], ['note', 'subshopID']);

        return $this->cleanupResultSet($orderNotes);
    }
}
