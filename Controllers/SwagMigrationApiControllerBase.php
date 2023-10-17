<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Controllers;

use Doctrine\DBAL\ConnectionException;
use Shopware\Models\User\Role;
use SwagMigrationConnector\Exception\PermissionDeniedException;
use SwagMigrationConnector\Exception\UnsecureRequestException;
use Symfony\Component\HttpFoundation\Response;

abstract class SwagMigrationApiControllerBase extends \Shopware_Controllers_Api_Rest
{
    /**
     * @throws PermissionDeniedException
     * @throws UnsecureRequestException
     */
    public function preDispatch()
    {
        parent::preDispatch();

        // This is a backport of SW-26764 to fix the unwanted DB values as INT / FLOAT behaviour in PHP8.1
        try {
            $connection = $this->container->get('dbal_connection');
            if (\is_object($connection->getWrappedConnection()) && \method_exists($connection->getWrappedConnection(), 'setAttribute')) {
                $connection->getWrappedConnection()->setAttribute(\PDO::ATTR_STRINGIFY_FETCHES, true);
            }
        } catch (ConnectionException $exception) {
            // nth
        }

        $pluginName = $this->container->getParameter('swag_migration_connector.plugin_name');
        $pluginConfig = $this->container->get('shopware.plugin.config_reader')->getByPluginName($pluginName);

        if (!$this->Request()->isSecure() && (bool) $pluginConfig['enforceSSL']) {
            throw new UnsecureRequestException('SSL required', Response::HTTP_UPGRADE_REQUIRED);
        }

        if ($this->container->initialized('auth')) {
            /** @var Role $role */
            $role = $this->container->get('auth')->getIdentity()->role;

            if ($role->getAdmin()) {
                return;
            }
        }

        throw new PermissionDeniedException('Permission denied. API user does not have sufficient rights for this action or could not be authenticated.', Response::HTTP_UNAUTHORIZED);
    }
}
