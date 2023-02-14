<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Service;

use League\Flysystem\FilesystemInterface;

class EsdService extends AbstractApiService
{
    /**
     * @var bool
     */
    private $isFileSystemExisting;

    /**
     * @var FilesystemInterface|null
     */
    private $fileSystem;

    public function __construct(
        $fileSystem
    ) {
        $this->isFileSystemExisting = \is_subclass_of($fileSystem, 'League\Flysystem\FilesystemInterface');
        $this->fileSystem = $fileSystem;
    }

    /**
     * @return bool
     */
    public function existsFileSystem()
    {
        return $this->isFileSystemExisting;
    }

    /**
     * @param string $filePath
     *
     * @return bool
     */
    public function fileExists($filePath)
    {
        if ($this->isFileSystemExisting && $this->fileSystem !== null) {
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
        if ($this->isFileSystemExisting && $this->fileSystem !== null) {
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
        if ($this->isFileSystemExisting && $this->fileSystem !== null) {
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
}
