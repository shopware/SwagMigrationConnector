<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Shopware_Controllers_Api_SwagMigrationCustomers extends Shopware_Controllers_Api_Rest
{
    public function indexAction()
    {
        $offset = (int) $this->Request()->getParam('offset', 0);
        $limit = (int) $this->Request()->getParam('limit', 250);
        $customerService = $this->container->get('swag_migration_api.service.customer_service');

        $customers = $customerService->getCustomers($offset, $limit);

        $this->view->assign(['success' => true, 'data' => $customers]);
    }
}
