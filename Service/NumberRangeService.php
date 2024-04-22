<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Service;

use Shopware\Components\Model\ModelManager;
use Shopware\Models\Shop\Shop;
use SwagMigrationConnector\Repository\NumberRangeRepository;

class NumberRangeService extends AbstractApiService
{
    /**
     * @var NumberRangeRepository
     */
    private $repository;

    /**
     * @var ModelManager
     */
    private $modelManager;

    public function __construct(NumberRangeRepository $repository, ModelManager $modelManager)
    {
        $this->repository = $repository;
        $this->modelManager = $modelManager;
    }

    /**
     * @return list<array{id: string, number: string, name: string, desc: string, _locale: string, prefix: string}>
     */
    public function getNumberRanges()
    {
        $productOrderNumberPrefixSerialized = $this->repository->fetchPrefix();
        if (\PHP_VERSION_ID >= 70000) {
            $prefix = \unserialize($productOrderNumberPrefixSerialized, ['allowedClasses' => false]);
        } else {
            $prefix = \unserialize($productOrderNumberPrefixSerialized);
        }

        if (!$prefix) {
            $prefix = '';
        }

        $defaultShop = $this->modelManager->getRepository(Shop::class)->getDefault();
        // represents the main language of the migrated shop
        $locale = \str_replace('_', '-', $defaultShop->getLocale()->getLocale());

        $returnedRanges = [];
        foreach ($this->repository->fetch() as $numberRange) {
            $numberRange['_locale'] = $locale;
            $numberRange['prefix'] = '';
            if ($numberRange['name'] === 'articleordernumber') {
                $numberRange['prefix'] = $prefix;
            }

            $returnedRanges[] = $numberRange;
        }

        return $returnedRanges;
    }
}
