<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Shopware_Controllers_Backend_SwagMigrationConnector extends Shopware_Controllers_Backend_ExtJs
{
    public function preDispatch()
    {
        parent::preDispatch();
        $this->View()->addTemplateDir(\dirname(\dirname(__DIR__)) . '/Resources/views');
    }

    public function getPluginsAction()
    {
        $requestedPlugins = $this->getLocalPlugins();

        $response = $this->container->get('shopware_plugininstaller.store_client')->doPostRequest('/shopware/pluginsuccessoroptions', [
            'locale' => $this->getLocale(),
            'plugins' => $requestedPlugins,
        ], ['X-Shopware-Plugin-Successor-Secret' => 'ZfwRGnMdedoGj3Cxyz7NNQYApzUwyvr1337SroBtUVs2UuNakCgdC9IryTOpGiw']);

        $this->View()->assign($this->transformSbpResponse($response, \array_column($requestedPlugins, 'name')));
    }

    public function getRequirementsAction()
    {
        $this->View()->assign('data', $this->container->get('swag_migration_api.service.requirements_validator_service')->validate());
    }

    private function getLocalPlugins()
    {
        return $this->container->get('dbal_connection')
            ->createQueryBuilder()
            ->from('s_core_plugins', 'plugin')
            ->andWhere('plugin.active = 1')
            ->andWhere('plugin.capability_install = 1')
            ->andWhere('plugin.source != "Default"')
            ->andWhere('plugin.name != "SwagMigrationConnector"')
            ->select('name')
            ->execute()
            ->fetchAll();
    }

    /**
     * @return string
     */
    private function getLocale()
    {
        return Shopware()->Container()->get('auth')->getIdentity()->locale->getLocale();
    }

    private function transformSbpResponse(array $response, array $requestedPlugins)
    {
        $data = [];

        foreach ($response['plugins'] as $name => $plugin) {
            $data[] = [
                'name' => $name,
                'recommendations' => $plugin,
            ];

            if (($i = \array_search($name, $requestedPlugins, true)) !== false) {
                unset($requestedPlugins[$i]);
            }
        }

        // Add missing plugins
        foreach ($requestedPlugins as $requestedPlugin) {
            $data[] = [
                'name' => $requestedPlugin,
                'recommendations' => [
                    [
                        'releaseInfo' => null,
                        'sourcePlugin' => [
                            'localizedName' => $requestedPlugin,
                            'name' => $requestedPlugin,
                            'iconPath' => null,
                            'storeLink' => null,
                        ],
                        'type' => 'noSuccessor',
                    ],
                ],
            ];
        }

        // Fix invalid types
        foreach ($data as &$plugin) {
            foreach ($plugin['recommendations'] as &$recommendation) {
                if (empty($recommendation['targetPlugin']['releaseDate'])) {
                    continue;
                }
                $releaseDate = \strtotime($recommendation['targetPlugin']['releaseDate']);

                if (\time() >= $releaseDate) {
                    $recommendation['type'] = 'targetPluginReleased';
                }
            }
        }

        return [
            'total' => \count($data),
            'data' => $data,
            'success' => true,
        ];
    }
}
