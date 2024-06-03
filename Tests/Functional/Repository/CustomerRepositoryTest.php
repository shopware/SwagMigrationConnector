<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Tests\Functional\Repository;

use PHPUnit\Framework\TestCase;
use SwagMigrationConnector\Repository\CustomerRepository;
use SwagMigrationConnector\Tests\Functional\DatabaseTransactionTrait;

class CustomerRepositoryTest extends TestCase
{
    use DatabaseTransactionTrait;

    /**
     * @return void
     */
    public function testFetchShouldAddShopData()
    {
        $sql = file_get_contents(__DIR__ . '/_fixtures/customer.sql');
        static::assertTrue(\is_string($sql));

        $this->getContainer()->get('dbal_connection')->executeQuery($sql);

        $repository = $this->getCustomerRepository();

        $result = $repository->fetch();

        static::assertArrayHasKey('shop.customer_scope', $result[0]);
    }

    /**
     * @return CustomerRepository
     */
    private function getCustomerRepository()
    {
        return new CustomerRepository($this->getContainer()->get('dbal_connection'));
    }
}
