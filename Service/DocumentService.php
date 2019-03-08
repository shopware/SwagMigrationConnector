<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationAssistant\Service;

use League\Flysystem\FilesystemInterface;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Shop\Shop;
use SwagMigrationAssistant\Repository\ApiRepositoryInterface;
use SwagMigrationAssistant\Repository\DocumentRepository;

class DocumentService extends AbstractApiService
{
    /**
     * @var DocumentRepository
     */
    private $documentRepository;

    /**
     * @var FilesystemInterface
     */
    private $fileSystem;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @param ApiRepositoryInterface $documentRepository
     * @param FilesystemInterface    $fileSystem
     * @param ModelManager           $modelManager
     */
    public function __construct(
        ApiRepositoryInterface $documentRepository,
        FilesystemInterface $fileSystem,
        ModelManager $modelManager
    ) {
        $this->documentRepository = $documentRepository;
        $this->fileSystem = $fileSystem;
        $this->modelManager = $modelManager;
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
            $fetchedDocuments, [], ['document']
        );

        /** @var Shop $defaultShop */
        $defaultShop = $this->modelManager->getRepository(Shop::class)->getDefault();

        // represents the main language of the migrated shop
        $locale = $defaultShop->getLocale()->getLocale();

        foreach ($documents as &$document) {
            $document['_locale'] = $locale;
        }

        return $this->cleanupResultSet($documents);
    }

    /**
     * @param string $filePath
     *
     * @return bool
     */
    public function fileExists($filePath)
    {
        return $this->fileSystem->has($filePath);
    }

    /**
     * @param string $filePath
     *
     * @return false|int
     */
    public function getFileSize($filePath)
    {
        return $this->fileSystem->getSize($filePath);
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
            ->getOrderNumberByDocumentHash($documentHash)
        ;
    }
}
