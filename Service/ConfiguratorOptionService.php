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
use SwagMigrationConnector\Repository\ConfiguratorOptionRepository;

class ConfiguratorOptionService extends AbstractApiService
{
    /**
     * @var ConfiguratorOptionRepository
     */
    private $configuratorOptionRepository;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var MediaServiceInterface
     */
    private $mediaService;

    /**
     * @param ApiRepositoryInterface $customerGroupRepository
     * @param ApiRepositoryInterface $customerRepository
     * @param ModelManager $modelManager
     */
    public function __construct(
        ApiRepositoryInterface $customerGroupRepository,
        ModelManager $modelManager,
        MediaServiceInterface $mediaService
    ) {
        $this->configuratorOptionRepository = $customerGroupRepository;
        $this->modelManager = $modelManager;
        $this->mediaService = $mediaService;
    }

    /**
     * @param int $offset
     * @param int $limit
     *
     * @return array
     */
    public function getConfiguratorOptions($offset = 0, $limit = 250)
    {
        $fetchedConfiguratorOptions = $this->configuratorOptionRepository->fetch($offset, $limit);
        $options = $this->mapData($fetchedConfiguratorOptions, [], ['property']);

        /** @var Shop $defaultShop */
        $defaultShop = $this->modelManager->getRepository(Shop::class)->getDefault();

        // represents the main language of the migrated shop
        $locale = str_replace('_', '-', $defaultShop->getLocale()->getLocale());

        foreach ($options as $key => &$option) {
            if (isset($option['media']['path'])) {
                $option['media']['uri'] = $this->mediaService->getUrl($option['media']['path']);
            }

            $option['_locale'] = $locale;
        }
        unset($option);

        return $this->cleanupResultSet($options);
    }
}