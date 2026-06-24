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
        $shopId = (int) $this->connection->fetchColumn(
            'SELECT GREATEST((SELECT COALESCE(MAX(id), 0) FROM s_core_shops), (SELECT COALESCE(MAX(id), 0) FROM s_core_locales)) + 1'
        );
        $localeId = $shopId + 1;
        $customerId = $this->connection->fetchColumn('SELECT id FROM s_user ORDER BY id ASC LIMIT 1');

        static::assertTrue($customerId !== false);

        $this->connection->insert('s_core_locales', [
            'id' => $shopId,
            'locale' => 'yy_YY',
            'language' => 'Decoy language',
            'territory' => 'Decoy territory',
        ]);

        $this->connection->insert('s_core_locales', [
            'id' => $localeId,
            'locale' => 'zz_ZZ',
            'language' => 'Test language',
            'territory' => 'Test territory',
        ]);

        $this->createShopWithLocale($shopId, $localeId);

        $this->connection->update(
            's_user',
            ['language' => (string) $shopId],
            ['id' => (int) $customerId]
        );

        $customer = $this->findCustomerById($this->getCustomerRepository()->fetch(), (int) $customerId);

        static::assertTrue(\is_array($customer));
        static::assertSame('zz_ZZ', $customer['customerlanguage.locale']);
        static::assertNotSame('yy_YY', $customer['customerlanguage.locale']);
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

    /**
     * @return void
     */
    private function createShopWithLocale($shopId, $localeId)
    {
        $shop = $this->connection->fetchAssoc('SELECT * FROM s_core_shops ORDER BY id ASC LIMIT 1');

        static::assertTrue(\is_array($shop));

        $shop['id'] = $shopId;
        $shop['name'] = 'locale-test-' . $shopId;
        $shop['title'] = 'Locale Test ' . $shopId;
        $shop['host'] = 'locale-test-' . $shopId . '.example.com';
        $shop['locale_id'] = $localeId;
        $shop['default'] = 0;
        $shop['active'] = 1;

        $this->connection->insert('s_core_shops', $shop);
    }

    /**
     * @return array|null
     */
    private function findCustomerById(array $customers, $customerId)
    {
        foreach ($customers as $customer) {
            if ((int) $customer['customer.id'] === $customerId) {
                return $customer;
            }
        }

        return null;
    }
}
