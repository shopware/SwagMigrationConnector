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
    public function setUpOwn()
    {
        $sql = \file_get_contents(__DIR__ . '/_fixtures/translations.sql');
        Shopware()->Container()->get('dbal_connection')->exec($sql);
    }

    /**
     * @before
     */
    public function startTransactionBefore()
    {
        Shopware()->Container()->get('dbal_connection')->beginTransaction();
    }

    /**
     * @after
     */
    public function stopTransactionAfter()
    {
        Shopware()->Container()->get('dbal_connection')->rollBack();
    }

    public function testReadTranslationsShouldBeSuccessful()
    {
        $this->setUpOwn();
        $translationService = Shopware()->Container()->get('swag_migration_connector.service.translation_service');

        $translations = $translationService->getTranslations();

        static::assertCount(52, $translations);

        $firstTranslation = $translations[0];

        static::assertSame('config_mails', $firstTranslation['objecttype']);
        static::assertArrayHasKey('locale', $firstTranslation);
        static::assertSame('en-GB', $firstTranslation['locale']);
    }

    public function testReadTranslationsWithOffsetShouldBeSuccessful()
    {
        $this->setUpOwn();
        $translationService = Shopware()->Container()->get('swag_migration_connector.service.translation_service');

        $translations = $translationService->getTranslations(1);

        static::assertCount(51, $translations);

        $translation = $translations[5];

        static::assertSame('article', $translation['objecttype']);
        static::assertSame('122', $translation['objectkey']);
        static::assertArrayHasKey('locale', $translation);
        static::assertSame('en-GB', $translation['locale']);
    }

    public function testReadTranslationsWithLimitShouldBeSuccessful()
    {
        $this->setUpOwn();
        $translationService = Shopware()->Container()->get('swag_migration_connector.service.translation_service');

        $translations = $translationService->getTranslations(0, 2);

        static::assertCount(2, $translations);

        $firstTranslation = $translations[0];

        static::assertSame('config_mails', $firstTranslation['objecttype']);
        static::assertArrayHasKey('locale', $firstTranslation);
        static::assertSame('en-GB', $firstTranslation['locale']);
    }

    public function testReadTranslationsWithOffsetAndLimitShouldBeSuccessful()
    {
        $this->setUpOwn();
        $translationService = Shopware()->Container()->get('swag_migration_connector.service.translation_service');

        $translations = $translationService->getTranslations(10, 1);

        static::assertCount(1, $translations);

        $translation = $translations[0];

        static::assertSame('propertyoption', $translation['objecttype']);
        static::assertSame('1', $translation['objectkey']);
        static::assertArrayHasKey('locale', $translation);
        static::assertSame('en-GB', $translation['locale']);
    }

    public function testReadWithOutOfBoundsOffsetShouldOfferEmptyArray()
    {
        $this->setUpOwn();
        $translationService = Shopware()->Container()->get('swag_migration_connector.service.translation_service');

        $translations = $translationService->getTranslations(2000);

        static::assertEmpty($translations);
    }
}
