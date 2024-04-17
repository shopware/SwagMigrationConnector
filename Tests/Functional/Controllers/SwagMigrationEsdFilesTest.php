<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Tests\Functional\Controllers;

use PHPUnit\Framework\MockObject\MockObject;
use Shopware\Components\Api\Exception\ParameterMissingException;
use SwagMigrationConnector\Exception\FileNotFoundException;
use SwagMigrationConnector\Exception\FileNotReadableException;
use SwagMigrationConnector\Service\EsdService;
use SwagMigrationConnector\Tests\Functional\ContainerTrait;
use SwagMigrationConnector\Tests\Functional\Controllers\ControllerFactory\Arguments;
use SwagMigrationConnector\Tests\Functional\Controllers\ControllerFactory\ControllerFactory;

require __DIR__ . '/../../../Controllers/Api/SwagMigrationEsdFiles.php';

class SwagMigrationEsdFilesTest extends \Enlight_Components_Test_Controller_TestCase
{
    use ContainerTrait;

    /**
     * @var \Shopware_Controllers_Api_SwagMigrationEsdFiles
     */
    private $controller;

    /**
     * @var MockObject|EsdService
     */
    private $esdServiceMock;

    /**
     * @return void
     */
    public function setUpController()
    {
        $this->esdServiceMock = $this->createMock(EsdService::class);

        $arguments = new Arguments($this->getContainer());
        $arguments->addContainerService('swag_migration_connector.service.esd_service', $this->esdServiceMock);

        $this->controller = ControllerFactory::createController(\Shopware_Controllers_Api_SwagMigrationEsdFiles::class, $arguments);
    }

    /**
     * @return void
     */
    public function testGetActionWithoutParameter()
    {
        $this->setUpController();
        $this->expectException(ParameterMissingException::class);
        $this->expectExceptionMessage((new ParameterMissingException('id'))->getMessage());

        $this->controller->getAction();
    }

    /**
     * @return void
     */
    public function testGetActionWithExistingFileSystem()
    {
        $this->setUpController();
        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessage((new FileNotFoundException('File not found', 404))->getMessage());

        $this->esdServiceMock->method('existsFileSystem')->willReturn(true);
        $this->esdServiceMock->method('fileExists')->willReturn(false);
        $this->controller->Request()->setParam('id', base64_encode('test.txt'));

        $this->controller->getAction();
    }

    /**
     * @return void
     */
    public function testGetActionWithEmptyMimeType()
    {
        $this->setUpController();
        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessage((new FileNotFoundException('File not found', 404))->getMessage());

        $this->esdServiceMock->method('existsFileSystem')->willReturn(true);
        $this->esdServiceMock->method('fileExists')->willReturn(true);
        $this->esdServiceMock->method('getMimeType')->willReturn(null);
        $this->controller->Request()->setParam('id', base64_encode('test.txt'));

        $this->controller->getAction();
    }

    /**
     * @return void
     */
    public function testGetActionWithNotReadableFile()
    {
        $this->setUpController();
        $this->expectException(FileNotReadableException::class);
        $this->expectExceptionMessage((new FileNotReadableException('File is not readable'))->getMessage());

        $this->esdServiceMock->method('existsFileSystem')->willReturn(true);
        $this->esdServiceMock->method('fileExists')->willReturn(true);
        $this->esdServiceMock->method('getMimeType')->willReturn('application/pdf');
        $this->esdServiceMock->method('readFile')->willReturn(false);
        $this->controller->Request()->setParam('id', base64_encode('test.pdf'));

        $this->controller->getAction();
    }

    /**
     * @return void
     */
    public function testGetAction()
    {
        $this->setUpController();

        $file = \fopen(__DIR__ . '/fixture_file.txt', 'rb');
        $this->esdServiceMock->method('existsFileSystem')->willReturn(true);
        $this->esdServiceMock->method('fileExists')->willReturn(true);
        $this->esdServiceMock->method('getMimeType')->willReturn('application/pdf');
        $this->esdServiceMock->method('getFileSize')->willReturn(500);
        $this->esdServiceMock->method('readFile')->willReturn($file);
        $this->controller->Request()->setParam('id', base64_encode('test.pdf'));

        $this->controller->getAction();

        $counter = 0;
        foreach ($this->controller->Response()->getHeaders() as $header) {
            if (\strtolower($header['name']) === 'content-type') {
                static::assertSame('application/pdf', $header['value']);
                ++$counter;
            }
        }

        static::assertGreaterThan(0, $counter, 'AssertContentType: No Content-Type in headers found');
    }
}
