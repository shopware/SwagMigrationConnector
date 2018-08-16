<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationApi\Service;

use Shopware\Components\Model\ModelManager;
use Shopware\Models\Shop\Shop;
use SwagMigrationApi\Repository\EnvironmentRepository;

class EnvironmentService extends AbstractApiService
{
    const TABLES_TO_COUNT = [
        'products' => 's_articles_details',
        'customers' => 's_user',
        'categories' => 's_categories',
        'assets' => 's_media',
        'orders' => 's_order',
        'shops' => 's_core_shops',
        'shoppingWorlds' => 's_emotion',
    ];

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var EnvironmentRepository
     */
    private $repository;

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
     * @param ModelManager          $modelManager
     * @param EnvironmentRepository $environmentRepository
     * @param string                $version
     * @param string                $versionText
     * @param string                $revision
     */
    public function __construct(
        ModelManager $modelManager,
        EnvironmentRepository $environmentRepository,
        $version,
        $versionText,
        $revision
    ) {
        $this->modelManager = $modelManager;
        $this->repository = $environmentRepository;
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
        $locale = $defaultShop->getLocale()->getLocale();

        $resultSet = [
            'defaultShopLanguage' => $locale,
            'shopwareVersion' => $this->version,
            'versionText' => $this->versionText,
            'revision' => $this->revision,
            'structure' => $this->getShopStructure(),
        ];

        foreach (self::TABLES_TO_COUNT as $key => $table) {
            if ($key === 'categories') {
                $resultSet[$key] = $this->repository->getCategoryCount();
                continue;
            }
            $resultSet[$key] = $this->repository->getTableCount($table);
        }

        return $resultSet;
    }

    /**
     * @return array
     */
    private function getShopStructure()
    {
        $fetchedShops = $this->repository->getShops();
        $shops = $this->mapData($fetchedShops, [], ['shop']);

        foreach ($shops as $key => &$shop) {
            if (!empty($shop['main_id'])) {
                $shops[$shop['main_id']]['children'][] = $shop;
                unset($shops[$key]);
            }
        }

        return array_values($shops);
    }
}
