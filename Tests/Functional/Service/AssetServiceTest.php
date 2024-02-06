<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Tests\Functional\Service;

use PHPUnit\Framework\TestCase;
use SwagMigrationConnector\Tests\Functional\ContainerTrait;

class AssetServiceTest extends TestCase
{
    use ContainerTrait;

    /**
     * @return void
     */
    public function testReadAssetsShouldBeSuccessful()
    {
        $assetService = $this->getContainer()->get('swag_migration_connector.service.asset_service');

        $assets = $assetService->getAssets();

        static::assertCount(250, $assets);

        $firstAsset = $assets[0];

        static::assertArrayHasKey('uri', $firstAsset);
    }

    /**
     * @return void
     */
    public function testReadAssetsWithOffsetShouldBeSuccessful()
    {
        $assetService = $this->getContainer()->get('swag_migration_connector.service.asset_service');

        $assets = $assetService->getAssets(1);

        static::assertCount(250, $assets);

        $asset = $assets[5];

        static::assertArrayHasKey('uri', $asset);
    }

    /**
     * @return void
     */
    public function testReadAssetsWithLimitShouldBeSuccessful()
    {
        $assetService = $this->getContainer()->get('swag_migration_connector.service.asset_service');

        $assets = $assetService->getAssets(0, 2);

        static::assertCount(2, $assets);

        $firstAsset = $assets[0];

        static::assertArrayHasKey('uri', $firstAsset);
    }

    /**
     * @return void
     */
    public function testReadAssetsWithOffsetAndLimitShouldBeSuccessful()
    {
        $assetService = $this->getContainer()->get('swag_migration_connector.service.asset_service');

        $assets = $assetService->getAssets(250, 40);

        static::assertCount(40, $assets);

        $asset = $assets[0];

        static::assertArrayHasKey('uri', $asset);
    }

    /**
     * @return void
     */
    public function testReadWithOutOfBoundsOffsetShouldOfferEmptyArray()
    {
        $assetService = $this->getContainer()->get('swag_migration_connector.service.asset_service');

        $assets = $assetService->getAssets(2000);

        static::assertEmpty($assets);
    }
}
