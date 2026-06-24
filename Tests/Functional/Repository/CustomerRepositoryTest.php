<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Tests\Functional\Repository;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use SwagMigrationConnector\Repository\CustomerRepository;
use SwagMigrationConnector\Tests\Functional\DatabaseTransactionTrait;

class CustomerRepositoryTest extends TestCase
{
    use DatabaseTransactionTrait;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @return void
     */
    public function testFetchShouldAddShopData()
    {
        $sql = file_get_contents(__DIR__ . '/_fixtures/customer.sql');
        static::assertTrue(\is_string($sql));

        $this->connection->executeQuery($sql);

        $repository = $this->getCustomerRepository();

        $result = $repository->fetch();

        static::assertArrayHasKey('shop.customer_scope', $result[0]);
    }

    /**
     * @return void
     */
    public function testFetchReturnsLimitedBatchSize()
    {
        static::assertGreaterThan(1, \count($this->connection->executeQuery(
            'SELECT id FROM s_user'
        )->fetchAll()));

        static::assertCount(1, $this->getCustomerRepository()->fetch(0, 1));
    }

    /**
     * @return void
     */
    public function testFetchResolvesCustomerLanguageThroughShopLocale()
    {
        $shop = $this->connection->fetchAssoc(
            'SELECT id, locale_id
             FROM s_core_shops
             WHERE locale_id <> id
             ORDER BY id ASC
             LIMIT 1'
        );
        $offset = (int) $this->connection->fetchColumn('SELECT COUNT(*) FROM s_user');
        $sql = file_get_contents(__DIR__ . '/_fixtures/customer.sql');

        static::assertTrue(\is_array($shop));
        static::assertTrue(\is_string($sql));

        $this->connection->executeQuery($sql);
        $this->connection->update('s_user', ['language' => (string) $shop['id']], ['id' => 3]);

        $customer = $this->getCustomerRepository()->fetch($offset, 1)[0];

        static::assertSame('3', $customer['customer.id']);
        static::assertSame((string) $shop['id'], $customer['customer.language']);
        static::assertNotSame($customer['customer.language'], (string) $shop['locale_id']);
        static::assertSame((string) $shop['locale_id'], $customer['customerlanguage.id']);
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
     * @return CustomerRepository
     */
    private function getCustomerRepository()
    {
        return new CustomerRepository($this->connection);
    }
}
