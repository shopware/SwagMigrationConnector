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

    public function __construct(ApiRepositoryInterface $repository, ModelManager $modelManager)
    {
        $this->repository = $repository;
        $this->modelManager = $modelManager;
    }

    /**
     * @return array
     */
    public function getNumberRanges()
    {
        $numberRanges = $this->repository->fetch();
        $prefix = unserialize($this->repository->fetchPrefix(), ['allowedClasses' => false]);

        if (!$prefix) {
            $prefix = '';
        }

        /** @var Shop $defaultShop */
        $defaultShop = $this->modelManager->getRepository(Shop::class)->getDefault();
        // represents the main language of the migrated shop
        $locale = str_replace('_', '-', $defaultShop->getLocale()->getLocale());

        foreach ($numberRanges as &$numberRange) {
            $numberRange['_locale'] = $locale;
            $numberRange['prefix'] = $prefix;
        }

        return $numberRanges;
    }
}
