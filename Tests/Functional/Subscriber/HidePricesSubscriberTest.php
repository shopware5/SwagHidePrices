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
use SwagHidePrices\Subscriber\HidePricesSubscriber;
use SwagHidePrices\Tests\Functional\Mocks\ControllerMock;
use SwagHidePrices\Tests\UserLoginTrait;

class HidePricesSubscriberTest extends TestCase
{
    use UserLoginTrait;
    use DatabaseTransactionBehaviour;

    public function testHidePricesShouldBeFalse()
    {
        $sql = \file_get_contents(__DIR__ . '/_fixtures/plugin_config.sql');
        Shopware()->Container()->get('dbal_connection')->exec($sql);

        $controller = $this->getController();
        $eventArgs = new \Enlight_Event_EventArgs(['subject' => $controller]);

        $this->getSubscriber()->hidePrices($eventArgs);

        $result = $controller->View()->getAssign('ShowPrices');

        static::assertFalse($result);
    }

    public function testHidePricesShouldBeTrue()
    {
        $sql = \file_get_contents(__DIR__ . '/_fixtures/plugin_config.sql');
        Shopware()->Container()->get('dbal_connection')->exec($sql);

        $this->loginUser();

        $controller = $this->getController();
        $eventArgs = new \Enlight_Event_EventArgs(['subject' => $controller]);

        $this->getSubscriber()->hidePrices($eventArgs);

        $result = $controller->View()->getAssign('ShowPrices');

        $this->logOutUser();

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
