<?php


use SwagMigrationConnector\Exception\PermissionDeniedException;
use SwagMigrationConnector\Exception\UnsecureRequestException;
use SwagMigrationConnector\Service\ControllerReturnStruct;
use Symfony\Component\HttpFoundation\Response;

class Shopware_Controllers_Api_SwagMigrationProductOptionRelation extends Shopware_Controllers_Api_Rest
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
                Response::HTTP_UPGRADE_REQUIRED
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
            Response::HTTP_UNAUTHORIZED
        );
    }

    public function indexAction()
    {
        $offset = (int) $this->Request()->getParam('offset', 0);
        $limit = (int) $this->Request()->getParam('limit', 250);
        $productOptionRelationService = $this->container->get('swag_migration_connector.service.product_option_relation_service');

        $productOptionRelations = $productOptionRelationService->getProductOptionRelations($offset, $limit);
        $response = new ControllerReturnStruct($productOptionRelations, empty($productOptionRelations));

        $this->view->assign($response->jsonSerialize());
    }
}
