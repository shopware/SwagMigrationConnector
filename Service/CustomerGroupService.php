<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationAssistant\Service;

use Shopware\Components\Model\ModelManager;
use Shopware\Models\Shop\Shop;
use SwagMigrationAssistant\Repository\ApiRepositoryInterface;
use SwagMigrationAssistant\Repository\CustomerGroupRepository;
use SwagMigrationAssistant\Repository\CustomerRepository;

class CustomerGroupService extends AbstractApiService
{
    /**
     * @var CustomerGroupRepository
     */
    private $customerGroupRepository;

    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @param ApiRepositoryInterface $customerGroupRepository
     * @param ApiRepositoryInterface $customerRepository
     * @param ModelManager $modelManager
     */
    public function __construct(
        ApiRepositoryInterface $customerGroupRepository,
        ApiRepositoryInterface $customerRepository,
        ModelManager $modelManager
    ) {
        $this->customerGroupRepository = $customerGroupRepository;
        $this->customerRepository = $customerRepository;
        $this->modelManager = $modelManager;
    }

    /**
     * @param int $offset
     * @param int $limit
     *
     * @return array
     */
    public function getCustomerGroups($offset = 0, $limit = 250)
    {
        $fetchedCustomerGroups = $this->customerGroupRepository->fetch($offset, $limit);
        $groupIds = array_column($fetchedCustomerGroups, 'customerGroup.id');
        $customerGroups = $this->mapData($fetchedCustomerGroups, [], ['customerGroup']);

        $fetchedDiscounts = $this->customerRepository->fetchCustomerGroupDiscounts($groupIds);
        $discounts = $this->mapData($fetchedDiscounts, [], ['discount']);

        /** @var Shop $defaultShop */
        $defaultShop = $this->modelManager->getRepository(Shop::class)->getDefault();

        // represents the main language of the migrated shop
        $locale = $defaultShop->getLocale()->getLocale();

        foreach ($customerGroups as $key => &$customerGroup) {
            $customerGroup['_locale'] = $locale;
            if (isset($discounts[$customerGroup['id']])) {
                $customerGroup['discounts'] = $discounts[$customerGroup['id']];
            }
        }
        unset($customerGroup);

        return $this->cleanupResultSet($customerGroups);
    }
}