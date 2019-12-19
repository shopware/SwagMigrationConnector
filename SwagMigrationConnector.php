<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector;

use Shopware\Components\Plugin;
use SwagMigrationConnector\Service\MigrationConnectorCompilerPass;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class SwagMigrationConnector extends Plugin
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $version = $container->getParameter('shopware.release.version');
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/Resources/'));
        
        if (version_compare($version, '5.5.0', '<')) {
            $loader->load('services_54.xml');
            $container->addCompilerPass(new MigrationConnectorCompilerPass());
        } else {
            $loader->load('services_55.xml');
        }
    }
}
