<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use SwagMigrationConnector\Controllers\SwagMigrationApiControllerBase;

class Shopware_Controllers_Api_SwagMigrationProductPriceAttributes extends SwagMigrationApiControllerBase
{
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
            ->getAttributeConfiguration('s_articles_prices_attributes')
        ;

        $this->view->assign(['success' => true, 'data' => $attributeConfiguration]);
    }
}
