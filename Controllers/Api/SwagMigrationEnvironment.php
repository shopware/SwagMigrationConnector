<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Shopware_Controllers_Api_SwagMigrationEnvironment extends Shopware_Controllers_Api_SwagMigrationApi
{
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
