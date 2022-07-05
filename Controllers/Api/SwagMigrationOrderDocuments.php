<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Shopware\Models\User\Role;
use SwagMigrationConnector\Exception\DocumentNotFoundException;
use SwagMigrationConnector\Exception\PermissionDeniedException;
use SwagMigrationConnector\Exception\UnsecureRequestException;
use SwagMigrationConnector\Service\ControllerReturnStruct;
use Symfony\Component\HttpFoundation\Response;

class Shopware_Controllers_Api_SwagMigrationOrderDocuments extends Shopware_Controllers_Api_Rest
{
    /**
     * @throws \Exception
     */
    public function preDispatch()
    {
        parent::preDispatch();

        $pluginName = $this->container->getParameter('swag_migration_connector.plugin_name');
        $pluginConfig = $this->container->get('shopware.plugin.config_reader')->getByPluginName($pluginName);

        if (!$this->Request()->isSecure() && (bool) $pluginConfig['enforceSSL']) {
            throw new UnsecureRequestException('SSL required', Response::HTTP_UPGRADE_REQUIRED);
        }

        if ($this->container->initialized('auth')) {
            /** @var Role $role */
            $role = $this->container->get('auth')->getIdentity()->role;

            if ($role->getAdmin()) {
                return;
            }
        }

        throw new PermissionDeniedException('Permission denied. API user does not have sufficient rights for this action or could not be authenticated.', Response::HTTP_UNAUTHORIZED);
    }

    public function indexAction()
    {
        $offset = (int) $this->Request()->getParam('offset', 0);
        $limit = (int) $this->Request()->getParam('limit', 250);
        $documentService = $this->container->get('swag_migration_connector.service.document_service');

        $documents = $documentService->getDocuments($offset, $limit);
        $response = new ControllerReturnStruct($documents, empty($documents));

        $this->View()->assign($response->jsonSerialize());
    }

    /**
     * Delivers an order document by its hash for download.
     */
    public function getAction()
    {
        $documentService = $this->container->get('swag_migration_connector.service.document_service');
        $documentHash = $this->Request()->getParam('id', null);

        if (empty($documentHash)) {
            throw new DocumentNotFoundException('File not found', 404);
        }

        $filePath = $documentService->getFilePath($documentHash);
        if (!$documentService->fileExists($filePath)) {
            throw new DocumentNotFoundException('File not found', 404);
        }

        $orderNumber = $documentService->getOrderNumberByDocumentHash($documentHash);

        if ($documentService->existsFileSystem()) {
            $this->setDownloadHeaders($filePath, $orderNumber);

            $upstream = $documentService->readFile($filePath);
            $downstream = \fopen('php://output', 'rb');
            \stream_copy_to_stream($upstream, $downstream);

            fclose($downstream);
            fclose($upstream);
            exit;
        } else {
            // Disable Smarty rendering
            $this->Front()->Plugins()->ViewRenderer()->setNoRender();
            $this->Front()->Plugins()->Json()->setRenderer(false);

            $this->setDownloadHeaders($filePath, $orderNumber);

            \readfile($filePath);
        }
    }

    /**
     * @param string $filePath
     * @param string $orderNumber
     */
    private function setDownloadHeaders($filePath, $orderNumber)
    {
        $documentService = $this->container->get('swag_migration_connector.service.document_service');

        $response = $this->Response();
        $response->setHeader('Cache-Control', 'public');
        $response->setHeader('Content-Description', 'File Transfer');
        $response->setHeader('Content-disposition', 'attachment; filename=' . $orderNumber . '.pdf');
        $response->setHeader('Content-Type', 'application/pdf');
        $response->setHeader('Content-Transfer-Encoding', 'binary');
        $response->setHeader('Content-Length', $documentService->getFileSize($filePath));
        $response->sendHeaders();
        $response->sendResponse();
    }
}
