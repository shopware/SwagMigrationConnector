<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Shopware\Models\User\Role;
use SwagMigrationConnector\Exception\FileNotFoundException;
use SwagMigrationConnector\Exception\PermissionDeniedException;
use SwagMigrationConnector\Exception\UnsecureRequestException;
use Symfony\Component\HttpFoundation\Response;

class Shopware_Controllers_Api_SwagMigrationEsdFiles extends Shopware_Controllers_Api_Rest
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

    public function getAction()
    {
        $esdService = $this->container->get('swag_migration_connector.service.esd_service');
        $encodedFilePath = $this->Request()->getParam('id', null);

        if (empty($encodedFilePath)) {
            throw new FileNotFoundException('File not found', 404);
        }

        $filePath = base64_decode($encodedFilePath, true);
        if (!\is_string($filePath) || !$esdService->fileExists($filePath)) {
            throw new FileNotFoundException('File not found', 404);
        }

        if ($esdService->existsFileSystem()) {
            $fileName = basename($filePath);
            $mimeType = $esdService->getMimeType($filePath);

            if (!\is_string($mimeType)) {
                throw new FileNotFoundException('File not found', 404);
            }

            $this->setDownloadHeaders($filePath, $fileName, $mimeType);

            $upstream = $esdService->readFile($filePath);
            $downstream = \fopen('php://output', 'rb');
            \stream_copy_to_stream($upstream, $downstream);
        }
    }

    /**
     * @param string $filePath
     * @param string $fileName
     * @param string $mimeType
     */
    private function setDownloadHeaders($filePath, $fileName, $mimeType)
    {
        $esdService = $this->container->get('swag_migration_connector.service.esd_service');

        $response = $this->Response();
        $response->setHeader('Cache-Control', 'public');
        $response->setHeader('Content-Description', 'File Transfer');
        $response->setHeader('Content-disposition', 'attachment; filename=' . $fileName);
        $response->setHeader('Content-Type', $mimeType);
        $response->setHeader('Content-Transfer-Encoding', 'binary');
        $response->setHeader('Content-Length', $esdService->getFileSize($filePath));
        $response->sendHeaders();
        $response->sendResponse();
    }
}
