<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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

    public function hidePrices(\Enlight_Event_EventArgs $args): void
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
