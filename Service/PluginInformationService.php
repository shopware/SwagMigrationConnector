<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Service;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\PluginInstallerBundle\Context\PluginsByTechnicalNameRequest;
use Shopware\Bundle\PluginInstallerBundle\Service\PluginStoreService;
use Shopware\Bundle\PluginInstallerBundle\Struct\PluginStruct;

class PluginInformationService
{
    /**
     * @var PluginStoreService
     */
    private $pluginStoreService;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var string
     */
    private $shopwareVersion;

    /**
     * @param string $version
     */
    public function __construct(
        PluginStoreService $pluginStoreService,
        Connection $connection,
        $version
    ) {
        $this->pluginStoreService = $pluginStoreService;
        $this->connection = $connection;
        $this->shopwareVersion = $version;
    }

    /**
     * @param string
     *
     * @return bool|null
     */
    public function isUpdateRequired($locale)
    {
        try {
            $request = new PluginsByTechnicalNameRequest($locale, $this->shopwareVersion, ['SwagMigrationConnector']);
            $localVersion = $this->getInstalledVersion();

            $pluginStruct = $this->pluginStoreService->getPlugin($request);

            if (!$pluginStruct instanceof PluginStruct) {
                return null;
            }

            return \version_compare(
                $pluginStruct->getVersion(),
                $localVersion
            ) === 1;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @return string
     */
    private function getInstalledVersion()
    {
        $query = $this->connection->createQueryBuilder();
        $query->select(['plugin.version'])
            ->from('s_core_plugins', 'plugin')
            ->where('plugin.name = "SwagMigrationConnector"');

        return $query->execute()->fetch(\PDO::FETCH_COLUMN);
    }
}
