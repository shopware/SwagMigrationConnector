<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Service;

use Shopware\Components\Model\ModelManager;
use Shopware\Models\Shop\Shop;
use SwagMigrationConnector\Repository\ApiRepositoryInterface;
use SwagMigrationConnector\Repository\ShopRepository;

class ShopService extends AbstractApiService
{
    /**
     * @var ShopRepository
     */
    private $repository;

    /**
     * @var ModelManager
     */
    private $modelManager;

    public function __construct(ApiRepositoryInterface $repository, ModelManager $modelManager)
    {
        $this->repository = $repository;
        $this->modelManager = $modelManager;
    }

    /**
     * @return array
     */
    public function getShops()
    {
        $fetchedSalesChannels = $this->repository->fetch();
        $salesChannels = $this->mapData($fetchedSalesChannels, [], ['shop', 'locale', 'currency']);

        /** @var Shop $defaultShop */
        $defaultShop = $this->modelManager->getRepository(Shop::class)->getDefault();
        // represents the main language of the migrated shop
        $defaultLocale = \str_replace('_', '-', $defaultShop->getLocale()->getLocale());

        foreach ($salesChannels as $key => &$salesChannel) {
            $salesChannel['locale'] = \str_replace('_', '-', $salesChannel['locale']);
            $salesChannel['_locale'] = $defaultLocale;
            if (!empty($salesChannel['main_id'])) {
                $salesChannels[$salesChannel['main_id']]['children'][] = $salesChannel;
                unset($salesChannels[$key]);
            }
        }
        $salesChannels = \array_values($salesChannels);

        return $this->cleanupResultSet($salesChannels);
    }
}
