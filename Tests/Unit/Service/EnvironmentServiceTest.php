<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationConnector\Tests\Unit\Service;

use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Shop\Currency;
use Shopware\Models\Shop\Locale;
use Shopware\Models\Shop\Repository as ShopRepository;
use Shopware\Models\Shop\Shop;
use SwagMigrationConnector\Repository\ConfigRepository;
use SwagMigrationConnector\Repository\EnvironmentRepository;
use SwagMigrationConnector\Service\EnvironmentService;
use SwagMigrationConnector\Service\PluginInformationService;

class EnvironmentServiceTest extends TestCase
{
    /**
     * @dataProvider getDifferentTimeZones
     *
     * @param string|null $timezone
     * @param string|null $expectedTimezone
     *
     * @return void
     */
    public function testGetEnvironmentInformationReturnsDatabaseTimezone($timezone, $expectedTimezone)
    {
        $environmentService = $this->createEnvironmentService($timezone);

        $environmentInformation = $environmentService->getEnvironmentInformation();

        static::assertArrayHasKey('timezone', $environmentInformation);
        static::assertSame($expectedTimezone, $environmentInformation['timezone']);
    }

    public function getDifferentTimeZones(): array
    {
        return [
            'null' => [
                'timezone' => null,
                'expectedTimezone' => null,
            ],
            'empty' => [
                'timezone' => '',
                'expectedTimezone' => null,
            ],
            'Europe/Berlin' => [
                'timezone' => 'Europe/Berlin',
                'expectedTimezone' => 'Europe/Berlin',
            ],
            'Europe/London' => [
                'timezone' => 'Europe/London',
                'expectedTimezone' => 'Europe/London',
            ],
        ];
    }

    /**
     * @param string|null $timezone
     *
     * @return EnvironmentService
     */
    private function createEnvironmentService($timezone)
    {
        return new EnvironmentService(
            $this->createModelManager(),
            $this->createEnvironmentRepository(),
            $this->createConfigRepository(),
            $this->createPluginInformationService(),
            '5.7.20',
            'Shopware 5.7.20',
            'test-revision',
            $timezone
        );
    }

    /**
     * @return ModelManager
     */
    private function createModelManager()
    {
        $modelManager = $this->getMockBuilder(ModelManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $modelManager->expects(static::exactly(2))
            ->method('getRepository')
            ->willReturnMap([
                [Shop::class, $this->createShopRepository()],
                [Currency::class, $this->createCurrencyRepository()],
            ]);

        return $modelManager;
    }

    /**
     * @return ShopRepository
     */
    private function createShopRepository()
    {
        $shopRepository = $this->getMockBuilder(ShopRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDefault'])
            ->getMock();

        $shopRepository->expects(static::once())
            ->method('getDefault')
            ->willReturn($this->createDefaultShop());

        return $shopRepository;
    }

    /**
     * @return EntityRepository
     */
    private function createCurrencyRepository()
    {
        $currencyRepository = $this->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['findOneBy'])
            ->getMock();

        $currencyRepository->expects(static::once())
            ->method('findOneBy')
            ->with(['default' => 1])
            ->willReturn($this->createDefaultCurrency());

        return $currencyRepository;
    }

    /**
     * @return EnvironmentRepository
     */
    private function createEnvironmentRepository()
    {
        $environmentRepository = $this->getMockBuilder(EnvironmentRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['getShops'])
            ->getMock();

        $environmentRepository->expects(static::once())
            ->method('getShops')
            ->willReturn([]);

        return $environmentRepository;
    }

    /**
     * @return ConfigRepository
     */
    private function createConfigRepository()
    {
        $configRepository = $this->getMockBuilder(ConfigRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['fetch'])
            ->getMock();

        $configRepository->expects(static::once())
            ->method('fetch')
            ->willReturn([]);

        return $configRepository;
    }

    /**
     * @return PluginInformationService
     */
    private function createPluginInformationService()
    {
        $pluginInformationService = $this->getMockBuilder(PluginInformationService::class)
            ->disableOriginalConstructor()
            ->setMethods(['isUpdateRequired'])
            ->getMock();

        $pluginInformationService->expects(static::once())
            ->method('isUpdateRequired')
            ->with('en-GB')
            ->willReturn(false);

        return $pluginInformationService;
    }

    /**
     * @return Shop
     */
    private function createDefaultShop()
    {
        $locale = new Locale();
        $locale->setLocale('en_GB');

        $shop = new Shop();
        $shop->setLocale($locale);

        return $shop;
    }

    /**
     * @return Currency
     */
    private function createDefaultCurrency()
    {
        $currency = new Currency();
        $currency->setCurrency('EUR');

        return $currency;
    }
}
