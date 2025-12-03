<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Tests\Functional\Repository;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use SwagMigrationConnector\Repository\ConfigRepository;
use SwagMigrationConnector\Tests\Functional\DatabaseTransactionTrait;

class ConfigRepositoryTest extends TestCase
{
    use DatabaseTransactionTrait;

    /**
     * @var Connection
     */
    private $connection;

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
     * @return void
     */
    public function testFetchReturnsUnserializedValues()
    {
        $repository = $this->getConfigRepository();

        $result = $repository->fetch();

        static::assertArrayHasKey('esdKey', $result);
        static::assertStringNotContainsString('s:', $result['esdKey']);

        static::assertArrayHasKey('installationDate', $result);
        static::assertStringNotContainsString('s:', $result['installationDate']);
    }

    /**
     * @return ConfigRepository
     */
    private function getConfigRepository()
    {
        return new ConfigRepository($this->connection);
    }
}
