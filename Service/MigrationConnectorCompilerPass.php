<?php

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