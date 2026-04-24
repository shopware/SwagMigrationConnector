<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Tests\Unit\Repository;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use SwagMigrationConnector\Repository\OrderRepository;

class OrderRepositoryTest extends TestCase
{
    /**
     * @return void
     */
    public function testGetDatabaseTimezoneReturnsTimezoneFromConnection()
    {
        $connection = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->setMethods(['fetchColumn'])
            ->getMock();

        $connection->expects(static::once())
            ->method('fetchColumn')
            ->willReturn('Europe/Berlin');

        $repository = new OrderRepository($connection);

        static::assertSame('Europe/Berlin', $repository->getDatabaseTimezone());
    }

    /**
     * @return void
     */
    public function testGetDatabaseTimezoneReturnsNullOnConnectionFailure()
    {
        $connection = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->setMethods(['fetchColumn'])
            ->getMock();

        $connection->expects(static::once())
            ->method('fetchColumn')
            ->willThrowException(new \Exception('Connection failed'));

        $repository = new OrderRepository($connection);

        static::assertNull($repository->getDatabaseTimezone());
    }

    /**
     * @return void
     */
    public function testGetDatabaseTimezoneReturnsNullForEmptyStrings()
    {
        $connection = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->setMethods(['fetchColumn'])
            ->getMock();

        $connection->expects(static::once())
            ->method('fetchColumn')
            ->willReturn('');

        $repository = new OrderRepository($connection);

        static::assertNull($repository->getDatabaseTimezone());
    }
}
