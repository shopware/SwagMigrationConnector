<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Tests\Functional\Service;

use PHPUnit\Framework\TestCase;

class TranslationServiceTest extends TestCase
{
    public function test_read_translations_should_be_successful()
    {
        $translationService = Shopware()->Container()->get('swag_migration_connector.service.translation_service');

        $translations = $translationService->getTranslations();

        $this->assertInternalType('array', $translations);
        $this->assertCount(123, $translations);

        $firstTranslation = $translations[0];

        $this->assertSame('config_mails', $firstTranslation['objecttype']);
        $this->assertArrayHasKey('locale', $firstTranslation);
        $this->assertSame('en-GB', $firstTranslation['locale']);
    }

    public function test_read_translations_with_offset_should_be_successful()
    {
        $translationService = Shopware()->Container()->get('swag_migration_connector.service.translation_service');

        $translations = $translationService->getTranslations(1);

        $this->assertInternalType('array', $translations);
        $this->assertCount(122, $translations);

        $translation = $translations[5];

        $this->assertSame('propertyoption', $translation['objecttype']);
        $this->assertSame('1', $translation['objectkey']);
        $this->assertArrayHasKey('locale', $translation);
        $this->assertSame('en-GB', $translation['locale']);
    }

    public function test_read_translations_with_limit_should_be_successful()
    {
        $translationService = Shopware()->Container()->get('swag_migration_connector.service.translation_service');

        $translations = $translationService->getTranslations(0, 2);

        $this->assertInternalType('array', $translations);
        $this->assertCount(2, $translations);

        $firstTranslation = $translations[0];

        $this->assertSame('config_mails', $firstTranslation['objecttype']);
        $this->assertArrayHasKey('locale', $firstTranslation);
        $this->assertSame('en-GB', $firstTranslation['locale']);
    }

    public function test_read_translations_with_offset_and_limit_should_be_successful()
    {
        $translationService = Shopware()->Container()->get('swag_migration_connector.service.translation_service');

        $translations = $translationService->getTranslations(6, 1);

        $this->assertInternalType('array', $translations);
        $this->assertCount(1, $translations);

        $translation = $translations[0];

        $this->assertSame('article', $translation['objecttype']);
        $this->assertSame('122', $translation['objectkey']);
        $this->assertArrayHasKey('locale', $translation);
        $this->assertSame('en-GB', $translation['locale']);
    }

    public function test_read_with_out_of_bounds_offset_should_offer_empty_array()
    {
        $translationService = Shopware()->Container()->get('swag_migration_connector.service.translation_service');

        $translations = $translationService->getTranslations(2000);

        $this->assertEmpty($translations);
    }
}
