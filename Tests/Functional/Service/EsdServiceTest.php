<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Tests\Functional\Service;

use League\Flysystem\Filesystem;
use PHPUnit\Framework\TestCase;
use SwagMigrationConnector\Service\EsdService;

class EsdServiceTest extends TestCase
{
    /**
     * @return void
     */
    public function testExistsFileSystem()
    {
        $service = new EsdService(null);
        static::assertFalse($service->existsFileSystem());

        $fileSystemMock = $this->createMock(Filesystem::class);
        $service = new EsdService($fileSystemMock);
        static::assertTrue($service->existsFileSystem());
    }

    /**
     * @return void
     */
    public function testFileExists()
    {
        $service = new EsdService(null);
        static::assertFalse($service->fileExists('testing file'));

        $fileSystemMock = $this->createMock(Filesystem::class);
        $fileSystemMock->method('has')->willReturn(true);
        $service = new EsdService($fileSystemMock);
        static::assertTrue($service->existsFileSystem());
    }

    /**
     * @return void
     */
    public function testGetMimeType()
    {
        $service = new EsdService(null);
        static::assertFalse($service->getMimeType('testing file'));

        $fileSystemMock = $this->createMock(Filesystem::class);
        $fileSystemMock->method('getMimetype')->willReturn('jpg');
        $service = new EsdService($fileSystemMock);
        static::assertSame('jpg', $service->getMimeType('testing file'));
    }

    /**
     * @return void
     */
    public function testGetFileSize()
    {
        $service = new EsdService(null);
        static::assertFalse($service->getFileSize('testing file'));

        $fileSystemMock = $this->createMock(Filesystem::class);
        $fileSystemMock->method('getSize')->willReturn(1500);
        $service = new EsdService($fileSystemMock);
        static::assertSame(1500, $service->getFileSize('testing file'));
    }

    /**
     * @return void
     */
    public function testReadFile()
    {
        $service = new EsdService(null);
        static::assertFalse($service->readFile('testing file'));

        $fileSystemMock = $this->createMock(Filesystem::class);
        $fileSystemMock->method('readStream')->willReturn(fopen('php://memory', 'r+'));
        $service = new EsdService($fileSystemMock);
        static::assertTrue(\is_resource($service->readFile('testing file')));
    }
}
