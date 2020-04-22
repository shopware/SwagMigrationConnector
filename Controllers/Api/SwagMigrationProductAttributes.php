<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Shopware\Models\User\Role;
use SwagMigrationConnector\Exception\PermissionDeniedException;
use SwagMigrationConnector\Exception\UnsecureRequestException;
use Symfony\Component\HttpFoundation\Response;

class Shopware_Controllers_Api_SwagMigrationProductAttributes extends Shopware_Controllers_Api_Rest
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
            throw new UnsecureRequestException('SSL required', Response::HTTP_UPGRADE_REQUIRED);
        }

        if ($this->container->initialized('Auth')) {
            /** @var Role $role */
            $role = $this->container->get('Auth')->getIdentity()->role;

            if ($role->getAdmin()) {
                return;
            }
        }

        throw new PermissionDeniedException('Permission denied. API user does not have sufficient rights for this action or could not be authenticated.', Response::HTTP_UNAUTHORIZED);
    }

    public function indexAction()
    {
        $offset = (int) $this->Request()->getParam('offset', 0);

        if ($offset !== 0) {
            $this->View()->assign([
                'success' => true,
                'data' => [],
            ]);

            return;
        }

        $attributeConfiguration = $this->container
            ->get('swag_migration_connector.service.attribute_service')
            ->getAttributeConfiguration('s_articles_attributes')
        ;

        $this->view->assign(['success' => true, 'data' => $attributeConfiguration]);
    }
}
