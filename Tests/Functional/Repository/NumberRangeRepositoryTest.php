<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Tests\Functional\Repository;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use SwagMigrationConnector\Repository\NumberRangeRepository;
use SwagMigrationConnector\Tests\Functional\ContainerTrait;

class NumberRangeRepositoryTest extends TestCase
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
        static::assertGreaterThan(4, \count($this->connection->executeQuery(
            'SELECT id FROM s_order_number'
        )->fetchAll()));

        static::assertCount(4, $this->getNumberRangeRepository()->fetch(0, 4));
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
     * @return NumberRangeRepository
     */
    private function getNumberRangeRepository()
    {
        return new NumberRangeRepository($this->connection);
    }
}
