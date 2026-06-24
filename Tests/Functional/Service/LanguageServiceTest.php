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
}
