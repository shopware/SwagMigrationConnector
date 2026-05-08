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
     * @dataProvider getDifferentDatabaseConfigs
     *
     * @param array<string, string|null> $dbConfig
     * @param string|null                $expectedTimezone
     *
     * @return void
     */
    public function testGetEnvironmentInformationReturnsDatabaseTimezone($dbConfig, $expectedTimezone)
    {
        $environmentService = $this->createEnvironmentService($dbConfig);

        $environmentInformation = $environmentService->getEnvironmentInformation();

        static::assertArrayHasKey('timezone', $environmentInformation);
        static::assertSame($expectedTimezone, $environmentInformation['timezone']);
    }

    /**
     * @return array<string, array{dbConfig: array<string, string|null>, expectedTimezone: string|null}>
     */
    public function getDifferentDatabaseConfigs()
    {
        return [
            'missing' => [
                'dbConfig' => [],
                'expectedTimezone' => null,
            ],
            'null' => [
                'dbConfig' => ['timezone' => null],
                'expectedTimezone' => null,
            ],
            'empty' => [
                'dbConfig' => ['timezone' => ''],
                'expectedTimezone' => null,
            ],
            'Europe/Berlin' => [
                'dbConfig' => ['timezone' => 'Europe/Berlin'],
                'expectedTimezone' => 'Europe/Berlin',
            ],
            'Europe/London' => [
                'dbConfig' => ['timezone' => 'Europe/London'],
                'expectedTimezone' => 'Europe/London',
            ],
        ];
    }

    /**
     * @param array<string, string|null> $dbConfig
     *
     * @return EnvironmentService
     */
    private function createEnvironmentService($dbConfig)
    {
        return new EnvironmentService(
            $this->createModelManager(),
            $this->createEnvironmentRepository(),
            $this->createConfigRepository(),
            $this->createPluginInformationService(),
            '5.7.20',
            'Shopware 5.7.20',
            'test-revision',
            $dbConfig
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
     * @return EntityRepository<Currency>
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
