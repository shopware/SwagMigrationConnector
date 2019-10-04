<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Service;

use SwagMigrationConnector\Repository\ApiRepositoryInterface;
use SwagMigrationConnector\Repository\VoteRepository;

class VoteService extends AbstractApiService
{
    /**
     * @var VoteRepository
     */
    private $voteRepository;

    /**
     * @param ApiRepositoryInterface $voteRepository
     */
    public function __construct(
        ApiRepositoryInterface $voteRepository
    ) {
        $this->voteRepository = $voteRepository;
    }

    /**
     * @param int $offset
     * @param int $limit
     *
     * @return array
     */
    public function getVotes($offset = 0, $limit = 250)
    {
        $fetchedVotes = $this->voteRepository->fetch($offset, $limit);
        $votes = $this->mapData($fetchedVotes, [], ['vote', 'mainShopId']);

        foreach ($votes as &$vote) {
            $vote['_locale'] = str_replace('_', '-', $vote['_locale']);
        }

        return $this->cleanupResultSet($votes);
    }
}
