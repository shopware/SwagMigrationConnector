<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Shopware\Models\User\Role;
use SwagMigrationApi\Exception\PermissionDeniedException;
use SwagMigrationApi\Exception\UnsecureRequestException;

/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Shopware_Controllers_Api_SwagMigrationEnvironment extends Shopware_Controllers_Api_Rest
{
    /**
     * @throws PermissionDeniedException
     * @throws UnsecureRequestException
     */
    public function preDispatch()
    {
        parent::preDispatch();

        if (!$this->Request()->isSecure()) {
            throw new UnsecureRequestException(
                'SSL required',
                426
            );
        }

        if ($this->container->initialized('Auth')) {
            /** @var Role $role */
            $role = $this->container->get('Auth')->getIdentity()->role;

            if (!$role->getAdmin()) {
                throw new PermissionDeniedException(
                    'Permission denied. API user does not have sufficient rights for this action.',
                    401
                );
            }
        }
    }

    public function indexAction()
    {
        $environmentService = $this->container->get('swag_migration_api.service.environment_service');

        $data = $environmentService->getEnvironmentInformation();

        $this->View()->assign([
            'success' => true,
            'data' => $data,
        ]);
    }
}