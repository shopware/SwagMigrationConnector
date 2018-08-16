<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationApi\Tests\Functional\Service;

class AssetServiceTest extends \PHPUnit_Framework_TestCase
{
    public function test_read_assets_should_be_successful()
    {
        $assetService = Shopware()->Container()->get('swag_migration_api.service.asset_service');

        $assets = $assetService->getAssets();

        $this->assertInternalType('array', $assets);
        $this->assertCount(250, $assets);

        $firstAsset = $assets[0];

        $this->assertArrayHasKey('album', $firstAsset);
        $this->assertArrayHasKey('settings', $firstAsset['album']);
        $this->assertArrayHasKey('uri', $firstAsset);
    }

    public function test_read_assets_with_offset_should_be_successful()
    {
        $assetService = Shopware()->Container()->get('swag_migration_api.service.asset_service');

        $assets = $assetService->getAssets(1);

        $this->assertInternalType('array', $assets);
        $this->assertCount(250, $assets);

        $asset = $assets[5];

        $this->assertArrayHasKey('album', $asset);
        $this->assertArrayHasKey('settings', $asset['album']);
        $this->assertArrayHasKey('uri', $asset);
    }

    public function test_read_assets_with_limit_should_be_successful()
    {
        $assetService = Shopware()->Container()->get('swag_migration_api.service.asset_service');

        $assets = $assetService->getAssets(0, 2);

        $this->assertInternalType('array', $assets);
        $this->assertCount(2, $assets);

        $firstAsset = $assets[0];

        $this->assertArrayHasKey('album', $firstAsset);
        $this->assertArrayHasKey('settings', $firstAsset['album']);
        $this->assertArrayHasKey('uri', $firstAsset);
    }

    public function test_read_assets_with_offset_and_limit_should_be_successful()
    {
        $assetService = Shopware()->Container()->get('swag_migration_api.service.asset_service');

        $assets = $assetService->getAssets(250, 40);

        $this->assertInternalType('array', $assets);
        $this->assertCount(40, $assets);

        $asset = $assets[0];

        $this->assertArrayHasKey('album', $asset);
        $this->assertArrayHasKey('settings', $asset['album']);
        $this->assertArrayHasKey('uri', $asset);
    }

    public function test_read_with_out_of_bounds_offset_should_offer_empty_array()
    {
        $assetService = Shopware()->Container()->get('swag_migration_api.service.asset_service');

        $assets = $assetService->getAssets(2000);

        $this->assertEmpty($assets);
    }
}