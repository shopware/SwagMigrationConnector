<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Tests\Functional;

use Shopware\Components\DependencyInjection\Container;

trait ContainerTrait
{
    /**
     * @return Container
     */
    public function getContainer()
    {
        $container = \SwagMigrationConnectorTestKernel::getKernel()->getContainer();

        if (!$container instanceof Container) {
            throw new \UnexpectedValueException('Container not found');
        }

        return $container;
    }
}
