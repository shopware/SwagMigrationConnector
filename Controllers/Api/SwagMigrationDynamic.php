<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Shopware\Components\Api\Exception\ParameterMissingException;
use Shopware\Models\User\Role;
use SwagMigrationAssistant\Exception\PermissionDeniedException;
use SwagMigrationAssistant\Exception\UnsecureRequestException;

class Shopware_Controllers_Api_SwagMigrationDynamic extends Shopware_Controllers_Api_Rest
{
    /**
     * @throws PermissionDeniedException
     * @throws UnsecureRequestException
     */
    public function preDispatch()
    {
        parent::preDispatch();

        $pluginName = $this->container->getParameter('swag_migration_api.plugin_name');
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

    /**
     * @throws ParameterMissingException
     */
    public function indexAction()
    {
        $offset = (int) $this->Request()->getParam('offset', 0);
        $limit = (int) $this->Request()->getParam('limit', 250);
        $table = (string) $this->Request()->getParam('table', '');

        if ($table === '') {
            throw new ParameterMissingException('The required parameter "table" is missing');
        }

        $repository = $this->container->get('swag_migration_api.repository.dynamic_repository');

        $fetchedData = $repository->fetch($table, $offset, $limit);

        $this->View()->assign([
            'success' => true,
            'data' => $fetchedData,
        ]);
    }
}