<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Tests\Functional\Controllers;

use Enlight_Controller_Request_RequestTestCase as Request;
use Shopware_Controllers_Api_SwagMigrationOrderDocuments as SwagMigrationOrderDocuments;
use SwagMigrationConnector\Exception\DocumentNotFoundException;
use SwagMigrationConnector\Exception\FileNotReadableException;
use SwagMigrationConnector\Exception\OrderNotFoundException;
use SwagMigrationConnector\Service\DocumentService;
use SwagMigrationConnector\Tests\Functional\ContainerTrait;
use SwagMigrationConnector\Tests\Functional\Controllers\ControllerFactory\Arguments;
use SwagMigrationConnector\Tests\Functional\Controllers\ControllerFactory\ControllerFactory;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

require __DIR__ . '/../../../Controllers/Api/SwagMigrationOrderDocuments.php';

class SwagMigrationOrderDocumentsTest extends \Enlight_Components_Test_Controller_TestCase
{
    use ContainerTrait;

    /**
     * @return void
     */
    public function testGetActionWithEmptyIdExpectsDocumentNotFoundException()
    {
        $arguments = new Arguments($this->getContainer());

        $controller = ControllerFactory::createController(SwagMigrationOrderDocuments::class, $arguments);

        $this->expectException(DocumentNotFoundException::class);
        $this->expectExceptionMessage('File not found');
        $this->expectExceptionCode(SymfonyResponse::HTTP_NOT_FOUND);

        $controller->getAction();
    }

    /**
     * @return void
     */
    public function testGetActionWithNotExistingIdExpectsDocumentNotFoundException()
    {
        $request = new Request();
        $request->setParam('id', 'anyIsHashThingy');

        $arguments = new Arguments($this->getContainer());
        $arguments->setRequest($request);

        $controller = ControllerFactory::createController(SwagMigrationOrderDocuments::class, $arguments);

        $this->expectException(DocumentNotFoundException::class);
        $this->expectExceptionMessage('File not found');
        $this->expectExceptionCode(SymfonyResponse::HTTP_NOT_FOUND);

        $controller->getAction();
    }

    /**
     * @return void
     */
    public function testGetActionExpectsOrderNotFoundException()
    {
        $request = new Request();
        $request->setParam('id', 'anyIsHashThingy');

        $documentServiceMock = $this->createMock(DocumentService::class);
        $documentServiceMock->method('getFilePath')->willReturn(__DIR__ . '/text.txt');
        $documentServiceMock->method('fileExists')->willReturn(true);
        $documentServiceMock->method('getOrderNumberByDocumentHash')->willReturn(false);

        $arguments = new Arguments($this->getContainer());
        $arguments->setRequest($request);
        $arguments->addContainerService('swag_migration_connector.service.document_service', $documentServiceMock);

        $controller = ControllerFactory::createController(SwagMigrationOrderDocuments::class, $arguments);

        $this->expectException(OrderNotFoundException::class);
        $this->expectExceptionMessage('Order with order number anyIsHashThingy not found');
        $this->expectExceptionCode(SymfonyResponse::HTTP_INTERNAL_SERVER_ERROR);

        $controller->getAction();
    }

    /**
     * @return void
     */
    public function testGetActionExpectsFileNotReadableException()
    {
        $request = new Request();
        $request->setParam('id', 'anyIsHashThingy');

        $documentServiceMock = $this->createMock(DocumentService::class);
        $documentServiceMock->method('getFilePath')->willReturn('/text.txt');
        $documentServiceMock->method('fileExists')->willReturn(true);
        $documentServiceMock->method('getOrderNumberByDocumentHash')->willReturn('SW10000');
        $documentServiceMock->method('existsFileSystem')->willReturn(true);
        $documentServiceMock->method('readFile')->willReturn(false);

        $arguments = new Arguments($this->getContainer());
        $arguments->setRequest($request);
        $arguments->addContainerService('swag_migration_connector.service.document_service', $documentServiceMock);

        $controller = ControllerFactory::createController(SwagMigrationOrderDocuments::class, $arguments);

        $this->expectException(FileNotReadableException::class);
        $this->expectExceptionMessage('File /text.txt is not readable');
        $this->expectExceptionCode(SymfonyResponse::HTTP_INTERNAL_SERVER_ERROR);

        $controller->getAction();
    }

    /**
     * @return void
     */
    public function testGetActionA()
    {
        $file = __DIR__ . '/fixture_file.txt';
        static::assertFileExists($file);

        $request = new Request();
        $request->setParam('id', 'anyIsHashThingy');

        $documentServiceMock = $this->createMock(DocumentService::class);
        $documentServiceMock->method('getFilePath')->willReturn($file);
        $documentServiceMock->method('fileExists')->willReturn(\file_exists($file));
        $documentServiceMock->method('getOrderNumberByDocumentHash')->willReturn('SW20001');
        $documentServiceMock->method('existsFileSystem')->willReturn(true);
        $documentServiceMock->method('readFile')->willReturn(\fopen($file, 'rb'));

        $arguments = new Arguments($this->getContainer());
        $arguments->setRequest($request);
        $arguments->addContainerService('swag_migration_connector.service.document_service', $documentServiceMock);

        $controller = ControllerFactory::createController(SwagMigrationOrderDocuments::class, $arguments);
        $controller->getAction();

        $counter = 0;
        foreach ($controller->Response()->getHeaders() as $header) {
            if (\strtolower($header['name']) === 'content-type') {
                static::assertSame('application/pdf', $header['value']);
                ++$counter;
            }
        }

        static::assertGreaterThan(0, $counter, 'AssertContentType: No Content-Type in headers found');
    }
}
