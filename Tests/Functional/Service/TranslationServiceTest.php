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
    /**
     * @return void
     */
    public function setUpOwn()
    {
        $sql = \file_get_contents(__DIR__ . '/_fixtures/translations.sql');
        Shopware()->Container()->get('dbal_connection')->exec($sql);
    }

    /**
     * @before
     *
     * @return void
     */
    public function startTransactionBefore()
    {
        Shopware()->Container()->get('dbal_connection')->beginTransaction();
    }

    /**
     * @after
     *
     * @return void
     */
    public function stopTransactionAfter()
    {
        Shopware()->Container()->get('dbal_connection')->rollBack();
    }

    /**
     * @return void
     */
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

    /**
     * @return void
     */
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

    /**
     * @return void
     */
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

    /**
     * @return void
     */
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

    /**
     * @return void
     */
    public function testReadWithOutOfBoundsOffsetShouldOfferEmptyArray()
    {
        $this->setUpOwn();
        $translationService = Shopware()->Container()->get('swag_migration_connector.service.translation_service');

        $translations = $translationService->getTranslations(2000);

        static::assertEmpty($translations);
    }
}
