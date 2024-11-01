<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Tests\Functional\Repository;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use SwagMigrationConnector\Repository\ProductRepository;
use SwagMigrationConnector\Tests\Functional\DatabaseTransactionTrait;

class ProductRepositoryTest extends TestCase
{
    use DatabaseTransactionTrait;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @return void
     */
    public function testFetchProductSeoMainCategoriesShouldReturnAllSeoMainCategories()
    {
        $sql = file_get_contents(__DIR__ . '/_fixtures/subshop_and_seo_main_categories.sql');
        static::assertTrue(\is_string($sql));

        $this->connection->executeQuery($sql);

        $repository = $this->getProductRepository();

        $result = $repository->fetchProductSeoMainCategories(['9', '272']);
        static::assertTrue(\is_array($result));
        static::assertCount(2, $result);
        static::assertArrayHasKey('9', $result);

        $productOne = $result['9'];
        static::assertTrue(\is_array($productOne));
        static::assertCount(2, $productOne);
        static::assertSame('1', $productOne[0]['shopId']);
        static::assertSame('14', $productOne[0]['categoryId']);
        static::assertSame('3', $productOne[1]['shopId']);
        static::assertSame('34', $productOne[1]['categoryId']);

        static::assertArrayHasKey('272', $result);
        $productTwo = $result['272'];
        static::assertTrue(\is_array($productTwo));
        static::assertCount(2, $productTwo);
        static::assertSame('1', $productTwo[0]['shopId']);
        static::assertSame('15', $productTwo[0]['categoryId']);
        static::assertSame('3', $productTwo[1]['shopId']);
        static::assertSame('16', $productTwo[1]['categoryId']);
    }

    /**
     * @return void
     */
    public function testFetchReturnsLimitedBatchSize()
    {
        static::assertGreaterThan(10, \count($this->connection->executeQuery(
            'SELECT id FROM s_articles_details'
        )->fetchAll()));

        static::assertCount(9, $this->getProductRepository()->fetch(0, 9));
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
     * @return ProductRepository
     */
    private function getProductRepository()
    {
        return new ProductRepository($this->connection);
    }
}
