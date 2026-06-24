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
    public function testReadResolvesCustomerLanguageThroughShopLocale()
    {
        $customerId = $this->connection->fetchColumn('SELECT id FROM s_user WHERE language IS NOT NULL ORDER BY id ASC LIMIT 1');
        $localeId = (int) $this->connection->fetchColumn('SELECT COALESCE(MAX(id), 0) + 1 FROM s_core_locales');

        static::assertTrue($customerId !== false);

        $shopId = (int) $this->connection->fetchColumn('SELECT language FROM s_user WHERE id = ?', [(int) $customerId]);

        static::assertGreaterThan(0, $shopId);

        $this->connection->executeStatement(
            'INSERT INTO s_core_locales (id, locale, language, territory)
             VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE locale = VALUES(locale), language = VALUES(language), territory = VALUES(territory)',
            [$shopId, 'yy_YY', 'Decoy language', 'Decoy territory']
        );

        $this->connection->insert('s_core_locales', [
            'id' => $localeId,
            'locale' => 'zz_ZZ',
            'language' => 'Test language',
            'territory' => 'Test territory',
        ]);

        static::assertSame(1, $this->connection->update('s_core_shops', ['locale_id' => $localeId], ['id' => $shopId]));

        $languageService = $this->getContainer()->get('swag_migration_connector.service.language_service');
        $languages = $languageService->getLanguages();
        $locales = \array_column($languages, 'locale');

        static::assertContains('zz-ZZ', $locales);
        static::assertNotContains('yy-YY', $locales);
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
