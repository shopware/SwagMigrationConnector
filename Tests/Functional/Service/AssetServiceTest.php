<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Tests\Functional\Service;

use PHPUnit\Framework\TestCase;

class AssetServiceTest extends TestCase
{
    public function test_read_assets_should_be_successful()
    {
        $assetService = Shopware()->Container()->get('swag_migration_connector.service.asset_service');

        $assets = $assetService->getAssets();

        static::assertCount(250, $assets);

        $firstAsset = $assets[0];

        static::assertArrayHasKey('uri', $firstAsset);
    }

    public function test_read_assets_with_offset_should_be_successful()
    {
        $assetService = Shopware()->Container()->get('swag_migration_connector.service.asset_service');

        $assets = $assetService->getAssets(1);

        static::assertCount(250, $assets);

        $asset = $assets[5];

        static::assertArrayHasKey('uri', $asset);
    }

    public function test_read_assets_with_limit_should_be_successful()
    {
        $assetService = Shopware()->Container()->get('swag_migration_connector.service.asset_service');

        $assets = $assetService->getAssets(0, 2);

        static::assertCount(2, $assets);

        $firstAsset = $assets[0];

        static::assertArrayHasKey('uri', $firstAsset);
    }

    public function test_read_assets_with_offset_and_limit_should_be_successful()
    {
        $assetService = Shopware()->Container()->get('swag_migration_connector.service.asset_service');

        $assets = $assetService->getAssets(250, 40);

        static::assertCount(40, $assets);

        $asset = $assets[0];

        static::assertArrayHasKey('uri', $asset);
    }

    public function test_read_with_out_of_bounds_offset_should_offer_empty_array()
    {
        $assetService = Shopware()->Container()->get('swag_migration_connector.service.asset_service');

        $assets = $assetService->getAssets(2000);

        static::assertEmpty($assets);
    }
}
