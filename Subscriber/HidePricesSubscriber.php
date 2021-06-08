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

namespace SwagHidePrices\Subscriber;

use Enlight\Event\SubscriberInterface;
use SwagHidePrices\Services\HidePricesServiceInterface;

class HidePricesSubscriber implements SubscriberInterface
{
    /**
     * @var HidePricesServiceInterface
     */
    private $hidePricesService;

    /**
     * @var string
     */
    private $pluginPath;

    public function __construct(HidePricesServiceInterface $hidePricesService, string $pluginPath)
    {
        $this->hidePricesService = $hidePricesService;
        $this->pluginPath = $pluginPath;
    }

    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Frontend' => 'hidePrices',
            'Enlight_Controller_Action_PostDispatchSecure_Widgets' => 'hidePrices',
            'Enlight_Controller_Action_PreDispatch_Widgets_Listing' => 'hidePrices',
        ];
    }

    public function hidePrices(\Enlight_Event_EventArgs $args)
    {
        /** @var \Enlight_Controller_Action $subject */
        $subject = $args->get('subject');

        /** @var \Enlight_View_Default $view */
        $view = $subject->View();

        $showPrices = $this->hidePricesService->shouldShowPrices();

        $view->addTemplateDir($this->pluginPath . '/Resources/views');
        $view->assign('ShowPrices', $showPrices);
    }
}
