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

        // If the implementation wrongly treats customer.language as a locale id,
        // it would resolve this decoy locale instead of going through the shop.
        if ($this->connection->fetchColumn('SELECT id FROM s_core_locales WHERE id = ?', [$shopId]) !== false) {
            $this->connection->update('s_core_locales', [
                'locale' => 'yy_YY',
                'language' => 'Decoy language',
                'territory' => 'Decoy territory',
            ], [
                'id' => $shopId,
            ]);
        } else {
            $this->connection->insert('s_core_locales', [
                'id' => $shopId,
                'locale' => 'yy_YY',
                'language' => 'Decoy language',
                'territory' => 'Decoy territory',
            ]);
        }

        // This is the locale the service should return after resolving customer.language
        // through shop.id -> shop.locale_id.
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
