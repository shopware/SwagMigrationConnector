<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Service;

use League\Flysystem\FilesystemInterface;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Shop\Shop;
use SwagMigrationConnector\Repository\ApiRepositoryInterface;
use SwagMigrationConnector\Repository\DocumentRepository;

class DocumentService extends AbstractApiService
{
    /**
     * @var DocumentRepository
     */
    private $documentRepository;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var bool
     */
    private $existsFileSystem;

    /**
     * @var FilesystemInterface|null
     */
    private $fileSystem;

    public function __construct(
        ApiRepositoryInterface $documentRepository,
        ?FilesystemInterface $fileSystem,
        ModelManager $modelManager
    ) {
        $this->documentRepository = $documentRepository;
        $this->modelManager = $modelManager;
        $this->existsFileSystem = ($fileSystem !== null && \is_subclass_of($fileSystem, 'League\Flysystem\FilesystemInterface'));
        $this->fileSystem = $fileSystem;
    }

    /**
     * @param int $offset
     * @param int $limit
     *
     * @return array
     */
    public function getDocuments($offset = 0, $limit = 250)
    {
        $fetchedDocuments = $this->documentRepository->fetch($offset, $limit);

        $documents = $this->mapData(
            $fetchedDocuments,
            [],
            ['document']
        );

        /** @var Shop $defaultShop */
        $defaultShop = $this->modelManager->getRepository(Shop::class)->getDefault();

        // represents the main language of the migrated shop
        $locale = \str_replace('_', '-', $defaultShop->getLocale()->getLocale());

        foreach ($documents as &$document) {
            $document['_locale'] = $locale;
        }

        return $this->cleanupResultSet($documents);
    }

    /**
     * @param string $documentHash
     *
     * @return string
     */
    public function getFilePath($documentHash)
    {
        if ($this->existsFileSystem) {
            return \sprintf('documents/%s.pdf', \basename($documentHash));
        }

        return \sprintf('%s/%s.pdf', Shopware()->Container()->getParameter('shopware.app.documentsdir'), \basename($documentHash));
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
        if ($this->existsFileSystem) {
            return $this->fileSystem->has($filePath);
        }

        return \file_exists($filePath);
    }

    /**
     * @param string $filePath
     *
     * @return false|int
     */
    public function getFileSize($filePath)
    {
        if ($this->existsFileSystem) {
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
        return $this->fileSystem->readStream($filePath);
    }

    /**
     * @param string $documentHash
     *
     * @return bool|string
     */
    public function getOrderNumberByDocumentHash($documentHash)
    {
        return $this->documentRepository
            ->getOrderNumberByDocumentHash($documentHash);
    }
}
