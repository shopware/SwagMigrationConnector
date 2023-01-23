<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Service;

use Doctrine\DBAL\Connection;
use League\Flysystem\FilesystemInterface;

class EsdService extends AbstractApiService
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var string
     */
    protected $esdFolderName;

    /**
     * @var bool
     */
    private $existsFileSystem;

    /**
     * @var FilesystemInterface|null
     */
    private $fileSystem;

    public function __construct(
        $fileSystem,
        Connection $connection
    ) {
        $this->existsFileSystem = $fileSystem !== null && \is_subclass_of($fileSystem, 'League\Flysystem\FilesystemInterface');
        $this->fileSystem = $fileSystem;
        $this->connection = $connection;
    }

    /**
     * @param string $fileName
     *
     * @return string
     */
    public function getFilePath($fileName)
    {
        if ($this->existsFileSystem) {
            if (!$this->esdFolderName && $this->getEsdDirectoryName() !== false) {
                $this->esdFolderName = \unserialize($this->getEsdDirectoryName(), ['allowed_classes' => false]);
            }

            if (!$this->esdFolderName) {
                return '';
            }

            return \sprintf('%s/%s', $this->esdFolderName, $fileName);
        }

        /** @var string $downloadDir */
        $downloadDir = Shopware()->Container()->getParameter('shopware.app.downloadsdir');

        return \sprintf('%s/%s', $downloadDir, \basename($fileName));
    }

    /**
     * @return bool
     */
    public function existsFileSystem()
    {
        return $this->existsFileSystem;
    }

    /**
     * @param string $filePath
     *
     * @return bool
     */
    public function fileExists($filePath)
    {
        if ($this->existsFileSystem && $this->fileSystem !== null) {
            return $this->fileSystem->has($filePath);
        }

        return \file_exists($filePath);
    }

    /**
     * @param string $filePath
     *
     * @return false|string
     */
    public function getMimeType($filePath)
    {
        if ($this->existsFileSystem && $this->fileSystem !== null) {
            return $this->fileSystem->getMimetype($filePath);
        }

        return \mime_content_type($filePath);
    }

    /**
     * @param string $filePath
     *
     * @return false|int
     */
    public function getFileSize($filePath)
    {
        if ($this->existsFileSystem && $this->fileSystem !== null) {
            return $this->fileSystem->getSize($filePath);
        }

        return \filesize($filePath);
    }

    /**
     * @param string $filePath
     *
     * @return false|resource
     */
    public function readFile($filePath)
    {
        if ($this->fileSystem === null) {
            return false;
        }

        return $this->fileSystem->readStream($filePath);
    }

    /**
     * @param string $id
     *
     * @return bool|string
     */
    public function getFilenameById($id)
    {
        return $this->connection->createQueryBuilder()
            ->select('file')
            ->from('s_articles_esd', 'esd')
            ->where('esd.id = :id')
            ->setParameter('id', $id)
            ->execute()
            ->fetchColumn();
    }

    /**
     * @return false|string
     */
    private function getEsdDirectoryName()
    {
        return $this->connection->createQueryBuilder()
            ->select('value')
            ->from('s_core_config_elements', 'config')
            ->where('config.name = :nameConfig')
            ->setParameter('nameConfig', 'esdKey')
            ->execute()
            ->fetchColumn();
    }
}
