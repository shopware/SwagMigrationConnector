<?php

class Shopware_Controllers_Api_SwagMigrationCustomers extends Shopware_Controllers_Api_Rest
{
    public function indexAction()
    {
        $offset = (int) $this->Request()->getParam('start', 0);
        $limit = (int) $this->Request()->getParam('limit', 250);
        $customerService = $this->container->get('swag_migration_api.service.customer_service');

        $customers = $customerService->getCustomers($offset, $limit);

        $this->view->assign(['success' => true, 'data' => $customers]);
    }
}