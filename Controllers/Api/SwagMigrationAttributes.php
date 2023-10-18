<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use SwagMigrationConnector\Controllers\SwagMigrationApiControllerBase;
use SwagMigrationConnector\Exception\ParameterMissingException;
use SwagMigrationConnector\Exception\UnknownTableException;

class Shopware_Controllers_Api_SwagMigrationAttributes extends SwagMigrationApiControllerBase
{
    /**
     * @throws ParameterMissingException
     * @throws UnknownTableException
     */
    public function indexAction()
    {
        $offset = (int) $this->Request()->getParam('offset', 0);
        $attributeTable = $this->Request()->getParam('attribute_table', null);

        if ($attributeTable === null) {
            throw new ParameterMissingException('The attribute_table parameter is missing.', 422);
        }

        if ($offset !== 0) {
            $this->View()->assign([
                'success' => true,
                'data' => [],
            ]);

            return;
        }

        $schemaManager = $this->container->get('dbal_connection')->getSchemaManager();

        if (!$schemaManager->tablesExist([$attributeTable])) {
            throw new UnknownTableException('The table: ' . $attributeTable . ' could not be found.');
        }

        $attributeConfiguration = $this->container
            ->get('swag_migration_connector.service.attribute_service')
            ->getAttributeConfiguration($attributeTable)
        ;

        $this->view->assign(['success' => true, 'data' => $attributeConfiguration]);
    }
}
