<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Shopware\Models\User\Role;
use SwagMigrationConnector\Exception\PermissionDeniedException;
use SwagMigrationConnector\Exception\UnsecureRequestException;
use SwagMigrationConnector\Service\ControllerReturnStruct;

class Shopware_Controllers_Api_SwagMigrationNumberRanges extends Shopware_Controllers_Api_Rest
{
    /**
     * @throws PermissionDeniedException
     * @throws UnsecureRequestException
     */
    public function preDispatch()
    {
        parent::preDispatch();

        $pluginName = $this->container->getParameter('swag_migration_connector.plugin_name');
        $pluginConfig = $this->container->get('shopware.plugin.config_reader')->getByPluginName($pluginName);

        if (!$this->Request()->isSecure() && (bool) $pluginConfig['enforceSSL']) {
            throw new UnsecureRequestException(
                'SSL required',
                426
            );
        }

        if ($this->container->initialized('Auth')) {
            /** @var Role $role */
            $role = $this->container->get('Auth')->getIdentity()->role;

            if ($role->getAdmin()) {
                return;
            }
        }

        throw new PermissionDeniedException(
            'Permission denied. API user does not have sufficient rights for this action or could not be authenticated.',
            401
        );
    }

    public function indexAction()
    {
        $service = $this->container->get('swag_migration_connector.service.number_range_service');
        $data = $service->getNumberRanges();
        $response = new ControllerReturnStruct($data, true);

        $this->View()->assign($response->jsonSerialize());
    }
}
