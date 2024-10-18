<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Tests\Functional\Repository;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use SwagMigrationConnector\Repository\ShopRepository;
use SwagMigrationConnector\Tests\Functional\ContainerTrait;

class ShopRepositoryTest extends TestCase
{
    use ContainerTrait;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @return void
     */
    public function testFetchReturnsLimitedBatchSize()
    {
        static::assertGreaterThan(1, \count($this->connection->executeQuery(
            'SELECT id FROM s_core_shops'
        )->fetchAll()));

        static::assertCount(1, $this->getShopRepository()->fetch(0, 1));
    }

    /**
     * @before
     *
     * @return void
     */
    protected function setUpMethod()
    {
        $this->connection = $this->getContainer()->get('dbal_connection');
    }

    /**
     * @return ShopRepository
     */
    private function getShopRepository()
    {
        return new ShopRepository($this->connection);
    }
}
