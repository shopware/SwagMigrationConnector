<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Service;

use Shopware\Components\DependencyInjection\Compiler\TagReplaceTrait;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MigrationConnectorCompilerPass implements CompilerPassInterface
{
    use TagReplaceTrait;

    public function process(ContainerBuilder $container)
    {
        $this->replaceArgumentWithTaggedServices($container, 'swag_migration_connector.repository.registry', 'shopware.migration.connector.repository', 0);
    }
}
