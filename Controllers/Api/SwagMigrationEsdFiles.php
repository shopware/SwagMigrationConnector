<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Shopware\Components\Api\Exception\ParameterMissingException;
use SwagMigrationConnector\Controllers\SwagMigrationApiControllerBase;
use SwagMigrationConnector\Exception\FileNotFoundException;
use SwagMigrationConnector\Exception\FileNotReadableException;

class Shopware_Controllers_Api_SwagMigrationEsdFiles extends SwagMigrationApiControllerBase
{
    /**
     * @return void
     */
    public function getAction()
    {
        $esdService = $this->container->get('swag_migration_connector.service.esd_service');
        $encodedFilePath = $this->Request()->getParam('id', null);

        if (empty($encodedFilePath)) {
            throw new ParameterMissingException('id');
        }

        $filePath = base64_decode($encodedFilePath, true);
        if (!$esdService->existsFileSystem()) { // The old filesystem is used
            $originalFilePath = \realpath('files');
            $realPath = \realpath('files/' . $filePath);

            if ($realPath === false || $originalFilePath !== \dirname(\dirname($realPath))) {
                throw new FileNotFoundException('File not found', 404);
            }

            $filePath = 'files/' . $filePath; // The exact folder has to be added
        }

        if (!\is_string($filePath) || !$esdService->fileExists($filePath)) {
            throw new FileNotFoundException('File not found', 404);
        }

        $fileName = basename($filePath);
        $mimeType = $esdService->getMimeType($filePath);

        if (!\is_string($mimeType)) {
            throw new FileNotFoundException('File not found', 404);
        }

        @set_time_limit(0);
        $this->setDownloadHeaders($filePath, $fileName, $mimeType);

        $upstream = $esdService->readFile($filePath);
        $downstream = \fopen('php://output', 'wb');

        if ($upstream === false || $downstream === false) {
            throw new FileNotReadableException('File is not readable');
        }

        ob_end_clean();
        while (!feof($upstream)) {
            $read = fread($upstream, 4096);
            if (!\is_string($read)) {
                continue;
            }
            fwrite($downstream, $read);
            flush();
        }
    }

    /**
     * @param string $filePath
     * @param string $fileName
     * @param string $mimeType
     *
     * @return void
     */
    private function setDownloadHeaders($filePath, $fileName, $mimeType)
    {
        $esdService = $this->container->get('swag_migration_connector.service.esd_service');
        $fileSize = $esdService->getFileSize($filePath);
        $fileSize = (string) (($fileSize === false) ? 0 : $fileSize);

        $response = $this->Response();
        $response->setHeader('Cache-Control', 'public');
        $response->setHeader('Content-Description', 'File Transfer');
        $response->setHeader('Content-disposition', 'attachment; filename=' . $fileName);
        $response->setHeader('Content-Type', $mimeType);
        $response->setHeader('Content-Transfer-Encoding', 'binary');
        $response->setHeader('Content-Length', $fileSize);
        $response->sendHeaders();
    }
}
