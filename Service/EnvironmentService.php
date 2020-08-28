<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Service;

use Shopware\Components\Model\ModelManager;
use Shopware\Models\Shop\Currency;
use Shopware\Models\Shop\Shop;
use SwagMigrationConnector\Repository\EnvironmentRepository;

class EnvironmentService extends AbstractApiService
{
    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var EnvironmentRepository
     */
    private $repository;

    /**
     * @var PluginInformationService
     */
    private $pluginInformationService;

    /**
     * @var string
     */
    private $version;

    /**
     * @var string
     */
    private $versionText;

    /**
     * @var string
     */
    private $revision;

    /**
     * @param string $version
     * @param string $versionText
     * @param string $revision
     */
    public function __construct(
        ModelManager $modelManager,
        EnvironmentRepository $environmentRepository,
        PluginInformationService $pluginInformationService,
        $version,
        $versionText,
        $revision
    ) {
        $this->modelManager = $modelManager;
        $this->repository = $environmentRepository;
        $this->pluginInformationService = $pluginInformationService;
        $this->version = $version;
        $this->versionText = $versionText;
        $this->revision = $revision;
    }

    /**
     * @return array
     */
    public function getEnvironmentInformation()
    {
        /** @var Shop $defaultShop */
        $defaultShop = $this->modelManager->getRepository(Shop::class)->getDefault();

        // represents the main language of the migrated shop
        $locale = str_replace('_', '-', $defaultShop->getLocale()->getLocale());

        /** @var Currency $defaultCurrency */
        $defaultCurrency = $this->modelManager->getRepository(Currency::class)->findOneBy([
            'default' => 1,
        ]);

        $resultSet = [
            'defaultShopLanguage' => $locale,
            'defaultCurrency' => $defaultCurrency->getCurrency(),
            'shopwareVersion' => $this->version,
            'versionText' => $this->versionText,
            'revision' => $this->revision,
            'additionalData' => $this->getAdditionalData(),
            'updateAvailable' => $this->pluginInformationService->isUpdateRequired($locale),
        ];

        return $resultSet;
    }

    /**
     * @return array
     */
    private function getAdditionalData()
    {
        $fetchedShops = $this->repository->getShops();
        $shops = $this->mapData($fetchedShops, [], ['shop']);

        foreach ($shops as $key => &$shop) {
            if (isset($shop['locale']['locale'])) {
                $shop['locale']['locale'] = str_replace('_', '-', $shop['locale']['locale']);
            }

            if (!empty($shop['main_id'])) {
                $shops[$shop['main_id']]['children'][] = $shop;
                unset($shops[$key]);
            }
        }

        return array_values($shops);
    }
}
