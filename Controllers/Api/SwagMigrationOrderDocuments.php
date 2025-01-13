<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use SwagMigrationConnector\Controllers\SwagMigrationApiControllerBase;
use SwagMigrationConnector\Exception\DocumentNotFoundException;
use SwagMigrationConnector\Exception\FileNotReadableException;
use SwagMigrationConnector\Exception\OrderNotFoundException;
use SwagMigrationConnector\Service\ControllerReturnStruct;
use Symfony\Component\HttpFoundation\Response;

class Shopware_Controllers_Api_SwagMigrationOrderDocuments extends SwagMigrationApiControllerBase
{
    /**
     * @return void
     */
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
     * Delivers an order document by its hash for download.#
     *
     * @return void
     */
    public function getAction()
    {
        $documentService = $this->container->get('swag_migration_connector.service.document_service');
        $documentHash = $this->Request()->getParam('id', null);

        if (empty($documentHash)) {
            throw new DocumentNotFoundException('File not found', Response::HTTP_NOT_FOUND);
        }

        $filePath = $documentService->getFilePath($documentHash);
        if (!$documentService->fileExists($filePath)) {
            throw new DocumentNotFoundException('File not found', Response::HTTP_NOT_FOUND);
        }

        $orderNumber = $documentService->getOrderNumberByDocumentHash($documentHash);
        if (!\is_string($orderNumber)) {
            throw new OrderNotFoundException(\sprintf('Order with order number %s not found', $documentHash), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        @set_time_limit(0);
        ob_end_clean();
        if ($documentService->existsFileSystem()) {
            $this->setDownloadHeaders($filePath, $orderNumber);

            $upstream = $documentService->readFile($filePath);
            $downstream = \fopen('php://output', 'wb');

            if ($upstream === false || $downstream === false) {
                $this->closeResource($upstream);
                $this->closeResource($downstream);

                throw new FileNotReadableException(\sprintf('File %s is not readable', $filePath), Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            while (!feof($upstream)) {
                $read = fread($upstream, 4096);
                if (!\is_string($read)) {
                    continue;
                }
                fwrite($downstream, $read);
                flush();
            }

            $this->closeResource($upstream);
            $this->closeResource($downstream);
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
     *
     * @return void
     */
    private function setDownloadHeaders($filePath, $orderNumber)
    {
        $documentService = $this->container->get('swag_migration_connector.service.document_service');
        $fileSize = $documentService->getFileSize($filePath);
        $fileSize = (string) (($fileSize === false) ? 0 : $fileSize);

        $response = $this->Response();
        $response->setHeader('Cache-Control', 'public');
        $response->setHeader('Content-Description', 'File Transfer');
        $response->setHeader('Content-disposition', 'attachment; filename=' . $orderNumber . '.pdf');
        $response->setHeader('Content-Type', 'application/pdf');
        $response->setHeader('Content-Transfer-Encoding', 'binary');
        $response->setHeader('Content-Length', $fileSize);
        $response->sendHeaders();
    }

    /**
     * @param resource|false $resource
     *
     * @return void
     */
    private function closeResource($resource)
    {
        if ($resource === false) {
            return;
        }

        \fclose($resource);
    }
}
