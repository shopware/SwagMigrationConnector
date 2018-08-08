<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationApi\Tests\Functional\Service;

class TranslationServiceTest extends \PHPUnit_Framework_TestCase
{
    public function test_read_translations_should_be_successful()
    {
        $translationService = Shopware()->Container()->get('swag_migration_api.service.translation_service');

        $translations = $translationService->getTranslations();

        $this->assertInternalType('array', $translations);
        $this->assertCount(124, $translations);

        $firstTranslation = $translations[0];

        $this->assertSame($firstTranslation['objecttype'], 'custom_facet');
        $this->assertArrayHasKey('locale', $firstTranslation);
        $this->assertSame('de_DE', $firstTranslation['locale']);
    }

    public function test_read_translations_with_offset_should_be_successful()
    {
        $translationService = Shopware()->Container()->get('swag_migration_api.service.translation_service');

        $translations = $translationService->getTranslations(1);

        $this->assertInternalType('array', $translations);
        $this->assertCount(123, $translations);

        $translation = $translations[5];

        $this->assertSame($translation['objecttype'], 'propertyoption');
        $this->assertArrayHasKey('locale', $translation);
        $this->assertSame('en_GB', $translation['locale']);
    }

    public function test_read_translations_with_limit_should_be_successful()
    {
        $translationService = Shopware()->Container()->get('swag_migration_api.service.translation_service');

        $translations = $translationService->getTranslations(0, 2);

        $this->assertInternalType('array', $translations);
        $this->assertCount(2, $translations);

        $firstTranslation = $translations[0];

        $this->assertSame($firstTranslation['objecttype'], 'custom_facet');
        $this->assertArrayHasKey('locale', $firstTranslation);
        $this->assertSame('de_DE', $firstTranslation['locale']);
    }

    public function test_read_translations_with_offset_and_limit_should_be_successful()
    {
        $translationService = Shopware()->Container()->get('swag_migration_api.service.translation_service');

        $translations = $translationService->getTranslations(6, 1);

        $this->assertInternalType('array', $translations);
        $this->assertCount(1, $translations);

        $translation = $translations[0];

        $this->assertSame($translation['objecttype'], 'propertyoption');
        $this->assertArrayHasKey('locale', $translation);
        $this->assertSame('en_GB', $translation['locale']);
    }
}
