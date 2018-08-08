<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationApi\Service;

use Shopware\Bundle\MediaBundle\MediaServiceInterface;
use SwagMigrationApi\Repository\ApiRepositoryInterface;
use SwagMigrationApi\Repository\AssetRepository;

class AssetService extends AbstractApiService
{
    /**
     * @var AssetRepository
     */
    private $assetRepository;

    /**
     * @var MediaServiceInterface
     */
    private $mediaService;

    /**
     * @param AssetRepository $assetRepository
     */
    public function __construct(ApiRepositoryInterface $assetRepository, MediaServiceInterface $mediaService)
    {
        $this->assetRepository = $assetRepository;
        $this->mediaService = $mediaService;
    }

    /**
     * @param int $offset
     * @param int $limit
     *
     * @return array
     */
    public function getAssets($offset = 0, $limit = 250)
    {
        $fetchedAssets = $this->assetRepository->fetch($offset, $limit);

        $assets = $this->mapData(
            $fetchedAssets, [], ['asset']
        );

        $resultSet = $this->prepareAssets($assets);

        return $this->cleanupResultSet($resultSet);
    }

    /**
     * @param array $assets
     *
     * @return array
     */
    private function prepareAssets(array $assets)
    {
        foreach ($assets as &$asset) {
            $asset['uri'] = $this->mediaService->getUrl($asset['path']);
        }
        unset($asset);

        return $assets;
    }
}
