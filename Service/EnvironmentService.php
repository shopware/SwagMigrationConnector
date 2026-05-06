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
use SwagMigrationConnector\Repository\ConfigRepository;
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
    private $environmentRepository;

    /**
     * @var ConfigRepository
     */
    private $configRepository;

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
     * @var string|null
     */
    private $timezone;

    /**
     * @param string $version
     * @param string $versionText
     * @param string $revision
     * @param string|null $timezone
     */
    public function __construct(
        ModelManager $modelManager,
        EnvironmentRepository $environmentRepository,
        ConfigRepository $configRepository,
        PluginInformationService $pluginInformationService,
        $version,
        $versionText,
        $revision,
        $timezone
    ) {
        $this->modelManager = $modelManager;
        $this->environmentRepository = $environmentRepository;
        $this->configRepository = $configRepository;
        $this->pluginInformationService = $pluginInformationService;
        $this->version = $version;
        $this->versionText = $versionText;
        $this->revision = $revision;
        $this->timezone = $timezone === '' ? null : $timezone;
    }

    /**
     * @return array
     */
    public function getEnvironmentInformation()
    {
        /** @var Shop $defaultShop */
        $defaultShop = $this->modelManager->getRepository(Shop::class)->getDefault();

        // represents the main language of the migrated shop
        $locale = \str_replace('_', '-', $defaultShop->getLocale()->getLocale());

        /** @var Currency $defaultCurrency */
        $defaultCurrency = $this->modelManager->getRepository(Currency::class)->findOneBy([
            'default' => 1,
        ]);

        $config = $this->configRepository->fetch();

        $resultSet = [
            'defaultShopLanguage' => $locale,
            'defaultCurrency' => $defaultCurrency->getCurrency(),
            'shopwareVersion' => $this->version,
            'versionText' => $this->versionText,
            'revision' => $this->revision,
            'additionalData' => $this->getAdditionalData(),
            'updateAvailable' => $this->pluginInformationService->isUpdateRequired($locale),
            'config' => $config,
            'timezone' => $this->timezone,
        ];

        return $resultSet;
    }

    /**
     * @return array
     */
    private function getAdditionalData()
    {
        $fetchedShops = $this->environmentRepository->getShops();
        $shops = $this->mapData($fetchedShops, [], ['shop']);

        foreach ($shops as $key => &$shop) {
            if (isset($shop['locale']['locale'])) {
                $shop['locale']['locale'] = \str_replace('_', '-', $shop['locale']['locale']);
            }

            if (!empty($shop['main_id'])) {
                $shops[$shop['main_id']]['children'][] = $shop;
                unset($shops[$key]);
            }
        }

        return \array_values($shops);
    }
}
