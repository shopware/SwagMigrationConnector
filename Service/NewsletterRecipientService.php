<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Service;

use SwagMigrationConnector\Repository\ApiRepositoryInterface;
use SwagMigrationConnector\Repository\NewsletterRecipientRepository;

class NewsletterRecipientService extends AbstractApiService
{
    /**
     * @var NewsletterRecipientRepository
     */
    private $newsletterRecipientRepository;

    public function __construct(ApiRepositoryInterface $customerRepository)
    {
        $this->newsletterRecipientRepository = $customerRepository;
    }

    /**
     * @param int $offset
     * @param int $limit
     *
     * @return array
     */
    public function getNewsletterRecipients($offset = 0, $limit = 250)
    {
        $fetchedNewsletterRecipients = $this->newsletterRecipientRepository->fetch($offset, $limit);
        $recipients = $this->mapData($fetchedNewsletterRecipients, [], ['recipient']);

        $ids = array_column($recipients, 'id');

        $resultSet = $this->assignAssociatedData($recipients, $ids);

        return $this->cleanupResultSet($resultSet);
    }

    /**
     * @return array
     */
    private function assignAssociatedData(array $newsletterRecipients, array $ids)
    {
        $shopsByCustomer = $this->newsletterRecipientRepository->getShopsAndLocalesByCustomer($ids);
        $defaultShop = $this->newsletterRecipientRepository->getDefaultShopAndLocaleByGroupId();
        $shops = $this->newsletterRecipientRepository->getShopsAndLocalesByGroupId();

        foreach ($newsletterRecipients as &$item) {
            if (isset($item['customer'], $shopsByCustomer[$item['id']][0]['shopId']) && $item['customer'] === '1') {
                $this->addShopAndLocaleByCustomer($item, $shopsByCustomer[$item['id']][0]);
                continue;
            }

            $this->addShopAndLocaleByGroupId($item, $defaultShop, $shops);
        }
        unset($item);

        return $newsletterRecipients;
    }

    private function addShopAndLocaleByGroupId(array &$item, array $defaultShop, array $shops)
    {
        if (isset($defaultShop[$item['groupID']][0])) {
            $item['shopId'] = $defaultShop[$item['groupID']][0]['shopId'];
            $item['_locale'] = str_replace('_', '-', $defaultShop[$item['groupID']][0]['locale']);
        } else {
            if (isset($shops[$item['groupID']])) {
                $shop = $shops[$item['groupID']][0];
                $shopId = $shop['shopId'];
                if (isset($shop['mainId'])) {
                    $shopId = $shop['mainId'];
                }

                $item['shopId'] = $shopId;
                $item['_locale'] = str_replace('_', '-', $shop['locale']);
            }
        }
    }

    private function addShopAndLocaleByCustomer(array &$item, array $shop)
    {
        $shopId = $shop['shopId'];
        if (isset($shop['mainId'])) {
            $shopId = $shop['mainId'];
        }

        $item['shopId'] = $shopId;
        $item['_locale'] = str_replace('_', '-', $shop['locale']);
    }
}
