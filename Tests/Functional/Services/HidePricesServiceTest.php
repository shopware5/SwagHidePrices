<?php
/**
 * Shopware Plugins
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this plugin can be used under
 * a proprietary license as set forth in our Terms and Conditions,
 * section 2.1.2.2 (Conditions of Usage).
 *
 * The text of our proprietary license additionally can be found at and
 * in the LICENSE file you have received along with this plugin.
 *
 * This plugin is distributed in the hope that it will be useful,
 * with LIMITED WARRANTY AND LIABILITY as set forth in our
 * Terms and Conditions, sections 9 (Warranty) and 10 (Liability).
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the plugin does not imply a trademark license.
 * Therefore any rights, title and interest in our trademarks
 * remain entirely with us.
 */

namespace SwagHidePrices\Tests\Functional\Services;

use PHPUnit\Framework\TestCase;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;
use SwagHidePrices\Services\HidePricesService;
use SwagHidePrices\Services\HidePricesServiceInterface;
use SwagHidePrices\Tests\UserLoginTrait;

class HidePricesServiceTest extends TestCase
{
    use UserLoginTrait;
    use DatabaseTransactionBehaviour;

    public function testShouldShowPricesShouldBeFalse(): void
    {
        $sql = \file_get_contents(__DIR__ . '/_fixtures/plugin_config.sql');
        Shopware()->Container()->get('dbal_connection')->exec($sql);

        $result = $this->getService()->shouldShowPrices();

        static::assertFalse($result);
    }

    public function testShouldShowPricesShouldBeTrue(): void
    {
        $sql = \file_get_contents(__DIR__ . '/_fixtures/plugin_config.sql');
        Shopware()->Container()->get('dbal_connection')->exec($sql);

        $this->loginUser();

        $result = $this->getService()->shouldShowPrices();

        $this->logOutUser();

        static::assertTrue($result);
    }

    /**
     * @dataProvider showPricesTestDataProvider
     */
    public function testShowPrices(int $showPricesLevel, array $validCustomerGroups, bool $userLoggedIn, bool $expectedResult): void
    {
        $reflectionMethod = (new \ReflectionClass(HidePricesService::class))->getMethod('showPrices');
        $reflectionMethod->setAccessible(true);

        $result = $reflectionMethod->invokeArgs($this->getService(), [$showPricesLevel, $validCustomerGroups, $userLoggedIn]);

        static::assertSame($expectedResult, $result);
    }

    public function showPricesTestDataProvider(): array
    {
        return [
            [0, [], false, false],
            [1, [], false, true],
            [2, [], false, false],
            [2, ['EK'], false, false],
            [2, ['EK'], true, true],
            [2, ['H'], true, false],
            [2, ['ANY'], true, false],
            [3, ['ANY'], true, false],
        ];
    }

    public function testIsUserLoggedInShouldBeFalse(): void
    {
        $reflectionMethod = (new \ReflectionClass(HidePricesService::class))->getMethod('isUserLoggedIn');
        $reflectionMethod->setAccessible(true);

        $result = $reflectionMethod->invoke($this->getService());

        static::assertFalse($result);
    }

    public function testIsUserLoggedInShouldBeTrue(): void
    {
        Shopware()->Container()->get('session')->offsetSet('sUserId', '12');

        $reflectionMethod = (new \ReflectionClass(HidePricesService::class))->getMethod('isUserLoggedIn');
        $reflectionMethod->setAccessible(true);

        $result = $reflectionMethod->invoke($this->getService());

        Shopware()->Container()->get('session')->offsetUnset('sUserId');

        static::assertTrue($result);
    }

    /**
     * @dataProvider getValidCustomerGroupsTestDataProvider
     */
    public function testGetValidCustomerGroups(array $config, array $expectedResult): void
    {
        $reflectionMethod = (new \ReflectionClass(HidePricesService::class))->getMethod('getValidCustomerGroups');
        $reflectionMethod->setAccessible(true);

        $result = $reflectionMethod->invoke($this->getService(), $config);

        static::assertSame($expectedResult, $result);
    }

    public function getValidCustomerGroupsTestDataProvider(): array
    {
        return [
            [['show_group' => ''], ['']],
            [['show_group' => 'Any, Foo, Bar'], ['Any', 'Foo', 'Bar']],
            [['show_group' => []], []],
            [['show_group' => ['EK']], ['EK']],
            [['show_group' => ['H']], ['H']],
            [['show_group' => ['Any']], ['Any']],
            [['show_group' => ['Any, Foo, Bar']], ['Any', 'Foo', 'Bar']],
            [['show_group' => ['Any', 'Foo', 'Bar']], ['Any', 'Foo', 'Bar']],
        ];
    }

    /**
     * @dataProvider normalizeCustomerGroupsTestDataProvider
     */
    public function testNormalizeCustomerGroups(string $customerGroupString, array $expectedResult): void
    {
        $reflectionMethod = (new \ReflectionClass(HidePricesService::class))->getMethod('normalizeCustomerGroups');
        $reflectionMethod->setAccessible(true);

        $result = $reflectionMethod->invoke($this->getService(), $customerGroupString);

        static::assertSame($expectedResult, $result);
    }

    public function normalizeCustomerGroupsTestDataProvider(): array
    {
        return [
            ['', ['']],
            [',', ['', '']],
            ['EK', ['EK']],
            ['EK, H, F', ['EK', 'H', 'F']],
            ['EK, H, F, E, G, lll, anyGroup', ['EK', 'H', 'F', 'E', 'G', 'lll', 'anyGroup']],
        ];
    }

    /**
     * @dataProvider isValidCustomerGroupTestDataProvider
     */
    public function testIsValidCustomerGroup(string $customerGroupKey, bool $expectedResult): void
    {
        $reflectionMethod = (new \ReflectionClass(HidePricesService::class))->getMethod('isValidCustomerGroup');
        $reflectionMethod->setAccessible(true);

        $result = $reflectionMethod->invoke($this->getService(), $customerGroupKey);

        static::assertSame($expectedResult, $result);
    }

    public function isValidCustomerGroupTestDataProvider(): array
    {
        return [
            ['EK', true],
            ['H', true],
            ['ANY', false],
            ['', false],
        ];
    }

    private function getHttpCachePlugin(): \Shopware_Plugins_Core_HttpCache_Bootstrap
    {
        return Shopware()->Container()->get('plugins')->Core()->get('HttpCache');
    }

    private function getService(): HidePricesServiceInterface
    {
        return Shopware()->Container()->get(HidePricesService::class);
    }
}
