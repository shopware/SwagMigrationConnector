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
use SwagMigrationConnector\Repository\DispatchRepository;

class DispatchService extends AbstractApiService
{
    /**
     * @var DispatchRepository
     */
    private $dispatchRepository;

    /**
     * @var ModelManager
     */
    private $modelManager;

    public function __construct(ApiRepositoryInterface $dispatchRepository, ModelManager $modelManager)
    {
        $this->dispatchRepository = $dispatchRepository;
        $this->modelManager = $modelManager;
    }

    /**
     * @param int $offset
     * @param int $limit
     *
     * @return array
     */
    public function getDispatches($offset = 0, $limit = 250)
    {
        $fetchedDispatches = $this->dispatchRepository->fetch($offset, $limit);
        $ids = \array_column($fetchedDispatches, 'dispatch.id');

        $dispatches = $this->mapData($fetchedDispatches, [], ['dispatch']);

        $resultSet = $this->assignAssociatedData($dispatches, $ids);

        return $this->cleanupResultSet($resultSet);
    }

    /**
     * @return array
     */
    private function assignAssociatedData(array $dispatches, array $ids)
    {
        $fetchedShippingCosts = $this->dispatchRepository->fetchShippingCosts($ids);
        $fetchedShippingCosts = $this->mapData($fetchedShippingCosts, [], ['shippingcosts', 'currencyShortName']);
        $shippingCountries = $this->dispatchRepository->fetchShippingCountries($ids);
        $paymentMethods = $this->dispatchRepository->fetchPaymentMethods($ids);
        $excludedCategories = $this->dispatchRepository->fetchExcludedCategories($ids);

        /** @var Shop $defaultShop */
        $defaultShop = $this->modelManager->getRepository(Shop::class)->getDefault();

        // represents the main language of the migrated shop
        $locale = \str_replace('_', '-', $defaultShop->getLocale()->getLocale());
        foreach ($dispatches as &$item) {
            if (isset($fetchedShippingCosts[$item['id']])) {
                $item['shippingCosts'] = $fetchedShippingCosts[$item['id']];
            }
            if (isset($shippingCountries[$item['id']])) {
                $item['shippingCountries'] = $shippingCountries[$item['id']];
            }
            if (isset($paymentMethods[$item['id']])) {
                $item['paymentMethods'] = \array_column($paymentMethods[$item['id']], 'paymentID');
            }
            if (isset($excludedCategories[$item['id']])) {
                $item['excludedCategories'] = \array_column($excludedCategories[$item['id']], 'categoryID');
            }

            $item['_locale'] = \str_replace('_', '-', $locale);
        }

        return $dispatches;
    }
}
