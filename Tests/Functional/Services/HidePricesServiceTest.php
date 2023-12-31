<?php
declare(strict_types=1);
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagHidePrices\Tests\Functional\Services;

use PHPUnit\Framework\TestCase;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;
use SwagHidePrices\Services\HidePricesService;
use SwagHidePrices\Services\HidePricesServiceInterface;
use SwagHidePrices\Tests\CustomerLoginTrait;

class HidePricesServiceTest extends TestCase
{
    use CustomerLoginTrait;
    use DatabaseTransactionBehaviour;

    public function testShouldShowPricesShouldBeFalse(): void
    {
        $sql = \file_get_contents(__DIR__ . '/../_fixtures/plugin_config.sql');
        static::assertIsString($sql);
        Shopware()->Container()->get('dbal_connection')->exec($sql);

        $result = $this->getService()->shouldShowPrices();

        static::assertFalse($result);
    }

    public function testShouldShowPricesShouldBeTrue(): void
    {
        $sql = \file_get_contents(__DIR__ . '/../_fixtures/plugin_config.sql');
        static::assertIsString($sql);
        Shopware()->Container()->get('dbal_connection')->exec($sql);

        $this->loginCustomer();

        $result = $this->getService()->shouldShowPrices();

        $this->logoutCustomer();

        static::assertTrue($result);
    }

    /**
     * @dataProvider showPricesTestDataProvider
     *
     * @param string[] $validCustomerGroups
     */
    public function testShowPrices(int $showPricesLevel, array $validCustomerGroups, bool $userLoggedIn, bool $expectedResult): void
    {
        $reflectionMethod = (new \ReflectionClass(HidePricesService::class))->getMethod('showPrices');
        $reflectionMethod->setAccessible(true);

        $result = $reflectionMethod->invokeArgs($this->getService(), [$showPricesLevel, $validCustomerGroups, $userLoggedIn]);

        static::assertSame($expectedResult, $result);
    }

    /**
     * @return array<array<int|string[]|bool>>
     */
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
     *
     * @param array<string, string|string[]|null> $config
     * @param string[]                            $expectedResult
     */
    public function testGetValidCustomerGroups(array $config, array $expectedResult): void
    {
        $reflectionMethod = (new \ReflectionClass(HidePricesService::class))->getMethod('getValidCustomerGroups');
        $reflectionMethod->setAccessible(true);

        $result = $reflectionMethod->invoke($this->getService(), $config);

        static::assertSame($expectedResult, $result);
    }

    /**
     * @return array<array<array<int|string, string|string[]|null>>>
     */
    public function getValidCustomerGroupsTestDataProvider(): array
    {
        return [
            [['show_group' => ''], ['']],
            [['show_group' => 'Any, Foo, Bar'], ['Any', 'Foo', 'Bar']],
            [['show_group' => null], []],
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
     *
     * @param string[] $expectedResult
     */
    public function testNormalizeCustomerGroups(string $customerGroupString, array $expectedResult): void
    {
        $reflectionMethod = (new \ReflectionClass(HidePricesService::class))->getMethod('normalizeCustomerGroups');
        $reflectionMethod->setAccessible(true);

        $result = $reflectionMethod->invoke($this->getService(), $customerGroupString);

        static::assertSame($expectedResult, $result);
    }

    /**
     * @return array<array<string|string[]>>
     */
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

    /**
     * @return array<array<string|bool>>
     */
    public function isValidCustomerGroupTestDataProvider(): array
    {
        return [
            ['EK', true],
            ['H', true],
            ['ANY', false],
            ['', false],
        ];
    }

    private function getService(): HidePricesServiceInterface
    {
        return Shopware()->Container()->get(HidePricesService::class);
    }
}
