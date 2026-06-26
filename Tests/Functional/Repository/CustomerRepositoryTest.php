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
             WHERE locale_id IS NOT NULL
             ORDER BY id ASC
             LIMIT 1'
        );
        static::assertTrue(\is_array($shop));

        $alternateLocaleId = $this->connection->fetchColumn(
            'SELECT id
             FROM s_core_locales
             WHERE id <> ?
             ORDER BY id ASC
             LIMIT 1',
            [(int) $shop['id']]
        );
        $offset = (int) $this->connection->fetchColumn('SELECT COUNT(*) FROM s_user');
        $sql = file_get_contents(__DIR__ . '/_fixtures/customer.sql');

        static::assertTrue($alternateLocaleId !== false);
        static::assertTrue(\is_string($sql));

        // Force a mismatch between the shop id and its locale id so the repository
        // must resolve customer.language through the shop relation, not directly as a locale id.
        $this->connection->update('s_core_shops', ['locale_id' => (int) $alternateLocaleId], ['id' => (int) $shop['id']]);
        $this->connection->executeQuery($sql);

        // Point the fixture customer at that shop id. The repository should then expose
        // the shop's locale_id as customerlanguage.id.
        $this->connection->update('s_user', ['language' => (string) $shop['id']], ['id' => 3]);

        $customer = $this->getCustomerRepository()->fetch($offset, 1)[0];

        static::assertSame('3', $customer['customer.id']);
        static::assertSame((string) $shop['id'], $customer['customer.language']);
        static::assertNotSame($customer['customer.language'], (string) $alternateLocaleId);
        static::assertSame((string) $alternateLocaleId, $customer['customerlanguage.id']);
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
