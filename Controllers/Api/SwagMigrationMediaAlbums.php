<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use SwagMigrationConnector\Controllers\SwagMigrationApiControllerBase;
use SwagMigrationConnector\Service\ControllerReturnStruct;

class Shopware_Controllers_Api_SwagMigrationMediaAlbums extends SwagMigrationApiControllerBase
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

        $mediaAlbumService = $this->container->get('swag_migration_connector.service.media_album_service');

        $assets = $mediaAlbumService->getAlbums();
        $response = new ControllerReturnStruct($assets, empty($assets));

        $this->view->assign($response->jsonSerialize());
    }
}
