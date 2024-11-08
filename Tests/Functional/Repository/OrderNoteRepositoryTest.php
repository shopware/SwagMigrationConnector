<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Tests\Functional\Repository;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use SwagMigrationConnector\Repository\OrderNoteRepository;
use SwagMigrationConnector\Tests\Functional\DatabaseTransactionTrait;

class OrderNoteRepositoryTest extends TestCase
{
    use DatabaseTransactionTrait;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @return void
     */
    public function testFetch()
    {
        $sql = file_get_contents(__DIR__ . '/_fixtures/order_notes.sql');
        static::assertTrue(\is_string($sql));

        $this->connection->executeQuery($sql);

        $expected = [
            [
                'note.sUniqueID' => 'unique-id-1',
                'note.userID' => '1',
                'note.articlename' => 'Kommode Shabby Chic',
                'note.articleID' => '68',
                'note.ordernumber' => 'SW10067',
                'note.datum' => '2024-10-17 13:54:26',
                'subshopID' => '1',
            ],
        ];

        $result = $this->getOrderNoteRepository()->fetch();
        unset($result[0]['note.id']);

        static::assertCount(5, $result);
        static::assertSame($expected[0], $result[0]);
    }

    /**
     * @return void
     */
    public function testFetchReturnsLimitedBatchSize()
    {
        $sql = file_get_contents(__DIR__ . '/_fixtures/order_notes.sql');
        static::assertTrue(\is_string($sql));

        $this->connection->executeQuery($sql);

        static::assertGreaterThan(4, \count($this->connection->executeQuery(
            'SELECT id FROM s_order_notes'
        )->fetchAll()));
        static::assertCount(4, $this->getOrderNoteRepository()->fetch(0, 4));
    }

    /**
     * @return void
     */
    public function testFetchWillReturnOnlyValidOrderNotes()
    {
        $sql = file_get_contents(__DIR__ . '/_fixtures/order_notes_with_invalid_data.sql');
        static::assertTrue(\is_string($sql));

        $this->connection->executeQuery($sql);
        $result = $this->getOrderNoteRepository()->fetch();

        static::assertCount(2, $result);

        foreach ($result as $wishListItem) {
            static::assertNotSame('unique-id-invalid-wishlist-item', $wishListItem['note.sUniqueID']);
        }
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
     * @return OrderNoteRepository
     */
    private function getOrderNoteRepository()
    {
        return new OrderNoteRepository($this->connection);
    }
}
