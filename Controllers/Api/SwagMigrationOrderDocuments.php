<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Shopware\Models\User\Role;
use SwagMigrationApi\Exception\DocumentNotFoundException;
use SwagMigrationApi\Exception\PermissionDeniedException;
use SwagMigrationApi\Exception\UnsecureRequestException;

/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Shopware_Controllers_Api_SwagMigrationOrderDocuments extends Shopware_Controllers_Api_Rest
{
    /**
     * @throws PermissionDeniedException
     * @throws UnsecureRequestException
     */
    public function preDispatch()
    {
        parent::preDispatch();

        if (!$this->Request()->isSecure()) {
            throw new UnsecureRequestException(
                'SSL required',
                426
            );
        }

        if ($this->container->initialized('Auth')) {
            /** @var Role $role */
            $role = $this->container->get('Auth')->getIdentity()->role;

            if (!$role->getAdmin()) {
                throw new PermissionDeniedException(
                    'Permission denied. API user does not have sufficient rights for this action.',
                    401
                );
            }
        }

        throw new Exception('Service currently not implemented');
    }

    public function indexAction()
    {
        $offset = (int) $this->Request()->getParam('offset', 0);
        $limit = (int) $this->Request()->getParam('limit', 250);
        $documentService = $this->container->get('swag_migration_api.service.document_service');

        $documents = $documentService->getDocuments($offset, $limit);

        $this->View()->assign([
            'success' => true,
            'data' => $documents,
        ]);
    }

    /**
     * Delivers an order document by its hash for download.
     */
    public function getAction()
    {
        $documentHash = $this->Request()->getParam('id', null);

        if (empty($documentHash)) {
            throw new DocumentNotFoundException(
                'File not found',
                404
            );
        }

        $filePath = sprintf('documents/%s.pdf', basename($documentHash));
        $documentService = $this->container->get('swag_migration_api.service.document_service');

        if (!$documentService->fileExists($filePath)) {
            throw new DocumentNotFoundException(
                'File not found',
                404
            );
        }
        $orderNumber = $documentService->getOrderNumberByDocumentHash($documentHash);

        $this->setDownloadHeaders($filePath, $orderNumber);

        $upstream = $documentService->readFile($filePath);
        $downstream = fopen('php://output', 'rb');

        while (!feof($upstream)) {
            fwrite($downstream, fread($upstream, 1024));
        }
    }

    /**
     * @param string $filePath
     * @param string $orderNumber
     */
    private function setDownloadHeaders($filePath, $orderNumber)
    {
        $documentService = $this->container->get('swag_migration_api.service.document_service');

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
