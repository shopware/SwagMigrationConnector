<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Service;

use Doctrine\DBAL\Connection;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Shop\Shop;

class NumberRangeService extends AbstractApiService
{
    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param ModelManager $modelManager
     */
    public function __construct(ModelManager $modelManager)
    {
        $this->modelManager = $modelManager;
        $this->connection = $modelManager->getConnection();
    }

    /**
     * @return array
     */
    public function getNumberRanges()
    {
        $numberRanges = $this->fetchNumberRanges();
        $prefix = unserialize($this->fetchPrefix(), ['allowedClasses' => false]);

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

    /**
     * @return array
     */
    private function fetchNumberRanges()
    {
        return $this->connection->createQueryBuilder()
            ->select('*')
            ->from('s_order_number')
            ->execute()
            ->fetchAll()
        ;
    }

    /**
     * @return mixed
     */
    private function fetchPrefix()
    {
        return $this->connection->createQueryBuilder()
            ->select('value')
            ->from('s_core_config_elements')
            ->where('name = "backendautoordernumberprefix"')
            ->execute()
            ->fetch(\PDO::FETCH_COLUMN)
        ;
    }
}
