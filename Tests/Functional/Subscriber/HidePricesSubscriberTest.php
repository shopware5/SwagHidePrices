<?php
declare(strict_types=1);
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagHidePrices\Tests\Functional\Subscriber;

use PHPUnit\Framework\TestCase;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;
use SwagHidePrices\Subscriber\HidePricesSubscriber;
use SwagHidePrices\Tests\CustomerLoginTrait;
use SwagHidePrices\Tests\Functional\Mocks\ControllerMock;

class HidePricesSubscriberTest extends TestCase
{
    use CustomerLoginTrait;
    use DatabaseTransactionBehaviour;

    public function testHidePricesShouldBeFalse(): void
    {
        $sql = \file_get_contents(__DIR__ . '/../_fixtures/plugin_config.sql');
        static::assertIsString($sql);
        Shopware()->Container()->get('dbal_connection')->exec($sql);

        $controller = $this->getController();
        $eventArgs = new \Enlight_Event_EventArgs(['subject' => $controller]);

        $this->getSubscriber()->hidePrices($eventArgs);

        $result = $controller->View()->getAssign('ShowPrices');

        static::assertFalse($result);
    }

    public function testHidePricesShouldBeTrue(): void
    {
        $sql = \file_get_contents(__DIR__ . '/../_fixtures/plugin_config.sql');
        static::assertIsString($sql);
        Shopware()->Container()->get('dbal_connection')->exec($sql);

        $this->loginCustomer();

        $controller = $this->getController();
        $eventArgs = new \Enlight_Event_EventArgs(['subject' => $controller]);

        $this->getSubscriber()->hidePrices($eventArgs);

        $result = $controller->View()->getAssign('ShowPrices');

        $this->logoutCustomer();

        static::assertTrue($result);
    }

    private function getSubscriber(): HidePricesSubscriber
    {
        return Shopware()->Container()->get(HidePricesSubscriber::class);
    }

    private function getController(): ControllerMock
    {
        $controller = new ControllerMock();

        $controller->setView(new \Enlight_View_Default(new \Enlight_Template_Manager()));

        return $controller;
    }
}
