<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Tests\Functional\Service;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use SwagMigrationConnector\Tests\Functional\DatabaseTransactionTrait;

class LanguageServiceTest extends TestCase
{
    use DatabaseTransactionTrait;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @return void
     */
    public function testReadIncludesCustomerLocalesOutsideShopLocales()
    {
        $localeId = (int) $this->connection->fetchColumn('SELECT COALESCE(MAX(id), 0) + 1 FROM s_core_locales');
        $customerId = $this->connection->fetchColumn('SELECT id FROM s_user ORDER BY id ASC LIMIT 1');

        static::assertTrue($customerId !== false);

        $this->connection->insert('s_core_locales', [
            'id' => $localeId,
            'locale' => 'zz_ZZ',
            'language' => 'Test language',
            'territory' => 'Test territory',
        ]);

        $this->connection->update(
            's_user',
            ['language' => (string) $localeId],
            ['id' => (int) $customerId]
        );

        $languageService = $this->getContainer()->get('swag_migration_connector.service.language_service');
        $languages = $languageService->getLanguages();

        static::assertContains('zz-ZZ', \array_column($languages, 'locale'));
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
}
