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
use SwagMigrationConnector\Repository\CurrencyRepository;

class CurrencyService extends AbstractApiService
{
    /**
     * @var CurrencyRepository
     */
    private $currencyRepository;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @param ApiRepositoryInterface $currencyRepository
     * @param ModelManager           $modelManager
     */
    public function __construct(ApiRepositoryInterface $currencyRepository, ModelManager $modelManager)
    {
        $this->currencyRepository = $currencyRepository;
        $this->modelManager = $modelManager;
    }

    /**
     * @param int $offset
     * @param int $limit
     *
     * @return array
     */
    public function getCurrencies($offset = 0, $limit = 250)
    {
        $currencies = $this->currencyRepository->fetch($offset, $limit);

        /** @var Shop $defaultShop */
        $defaultShop = $this->modelManager->getRepository(Shop::class)->getDefault();
        // represents the main language of the migrated shop
        $locale = str_replace('_', '-', $defaultShop->getLocale()->getLocale());

        foreach ($currencies as $key => &$currency) {
            $currency['_locale'] = $locale;
        }

        $currencies = $this->mapData($currencies, [], ['currency']);

        return $this->cleanupResultSet($currencies);
    }
}
