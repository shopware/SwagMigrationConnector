<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Shopware\Components\Api\Exception\ParameterMissingException;
use SwagMigrationConnector\Controllers\SwagMigrationApiControllerBase;
use SwagMigrationConnector\Service\ControllerReturnStruct;

class Shopware_Controllers_Api_SwagMigrationDynamic extends SwagMigrationApiControllerBase
{
    /**
     * @throws ParameterMissingException
     */
    public function indexAction()
    {
        $offset = (int) $this->Request()->getParam('offset', 0);
        $limit = (int) $this->Request()->getParam('limit', 250);
        $table = (string) $this->Request()->getParam('table', '');
        $filter = $this->Request()->getParam('filter', []);

        if ($table === '') {
            throw new ParameterMissingException('The required parameter "table" is missing');
        }

        $repository = $this->container->get('swag_migration_connector.repository.dynamic_repository');

        $fetchedData = $repository->fetch($table, $offset, $limit, $filter);
        $response = new ControllerReturnStruct($fetchedData, empty($fetchedData));

        $this->view->assign($response->jsonSerialize());
    }
}
