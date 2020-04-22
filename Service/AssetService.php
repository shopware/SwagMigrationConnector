<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Service;

use Shopware\Bundle\MediaBundle\MediaServiceInterface;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Shop\Shop;
use SwagMigrationConnector\Repository\ApiRepositoryInterface;
use SwagMigrationConnector\Repository\AssetRepository;

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
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @param AssetRepository $assetRepository
     */
    public function __construct(ApiRepositoryInterface $assetRepository, MediaServiceInterface $mediaService, ModelManager $modelManager)
    {
        $this->assetRepository = $assetRepository;
        $this->mediaService = $mediaService;
        $this->modelManager = $modelManager;
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
     * @return array
     */
    private function prepareAssets(array $assets)
    {
        /** @var Shop $defaultShop */
        $defaultShop = $this->modelManager->getRepository(Shop::class)->getDefault();

        // represents the main language of the migrated shop
        $locale = str_replace('_', '-', $defaultShop->getLocale()->getLocale());

        foreach ($assets as &$asset) {
            $asset['_locale'] = $locale;
            $asset['uri'] = $this->mediaService->getUrl($asset['path']);
        }
        unset($asset);

        return $assets;
    }
}
