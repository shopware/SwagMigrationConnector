<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Shopware\Models\User\Role;
use SwagMigrationAssistant\Exception\ParameterMissingException;
use SwagMigrationAssistant\Exception\PermissionDeniedException;
use SwagMigrationAssistant\Exception\UnknownTableException;
use SwagMigrationAssistant\Exception\UnsecureRequestException;

class Shopware_Controllers_Api_SwagMigrationAttributes extends Shopware_Controllers_Api_Rest
{
    /**
     * @throws PermissionDeniedException
     * @throws UnsecureRequestException
     */
    public function preDispatch()
    {
        parent::preDispatch();

        $pluginName = $this->container->getParameter('swag_migration_assistant.plugin_name');
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
     * @throws UnknownTableException
     */
    public function indexAction()
    {
        $attributeTable = $this->Request()->getParam('attribute_table', null);

        if ($attributeTable === null) {
            throw new ParameterMissingException(
                'The attribute_table parameter is missing.',
                422
            );
        }

        $schemaManager = $this->container->get('dbal_connection')->getSchemaManager();

        if (!$schemaManager->tablesExist([$attributeTable])) {
            throw new UnknownTableException('The table: ' . $attributeTable . ' could not be found.');
        }

        $attributeConfiguration = $this->container
            ->get('swag_migration_assistant.service.attribute_service')
            ->getAttributeConfiguration($attributeTable)
        ;

        $this->view->assign(['success' => true, 'data' => $attributeConfiguration]);
    }
}