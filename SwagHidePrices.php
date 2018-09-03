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

namespace SwagHidePrices;

use Enlight_Controller_Action;
use Enlight_View_Default;
use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\ActivateContext;
use Shopware\Components\Plugin\Context\DeactivateContext;
use Shopware_Plugins_Core_HttpCache_Bootstrap;

class SwagHidePrices extends Plugin
{
    /**
     * {@inheritdoc}
     */
    public function activate(ActivateContext $context)
    {
        $context->scheduleClearCache(ActivateContext::CACHE_LIST_DEFAULT);
    }

    /**
     * {@inheritdoc}
     */
    public function deactivate(DeactivateContext $context)
    {
        $context->scheduleClearCache(DeactivateContext::CACHE_LIST_DEFAULT);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Frontend' => 'hidePrices',
            'Enlight_Controller_Action_PostDispatchSecure_Widgets' => 'hidePrices',
            'Enlight_Controller_Action_PreDispatch_Widgets_Listing' => 'hidePrices',
        ];
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     */
    public function hidePrices(\Enlight_Event_EventArgs $args)
    {
        /** @var Enlight_Controller_Action $subject */
        $subject = $args->getSubject();

        /** @var Enlight_View_Default $view */
        $view = $subject->View();

        $config = $this->container->get('shopware.plugin.cached_config_reader')
            ->getByPluginName($this->getName(), $this->container->get('shop'));

        $userLoggedIn = (bool) $this->container->get('session')->get('sUserId');
        $configShowPrices = (int) $config['show_prices'];

        $showPrices = $this->showPrices($configShowPrices, $config, $userLoggedIn);

        /** @var Shopware_Plugins_Core_HttpCache_Bootstrap $httpCache */
        $httpCache = $this->container->get('plugins')->Core()->get('HttpCache');
        if ($httpCache !== null && $configShowPrices === 2 && $userLoggedIn && $this->checkIfHttpCacheIsActive()) {
            $httpCache->setNoCacheTag('price');
        }

        $view->addTemplateDir($this->getPath() . '/Resources/views');
        $view->assign('ShowPrices', $showPrices);
    }

    /**
     * @return bool
     */
    private function checkIfHttpCacheIsActive()
    {
        $result = $this->container->get('dbal_connection')->createQueryBuilder()
            ->select('active')
            ->from('s_core_plugins')
            ->where('name = "HttpCache"')
            ->setMaxResults(1)
            ->execute()
            ->fetch(\PDO::FETCH_ASSOC);

        return $result['active'] === 1;
    }

    /**
     * @param $configShowPrices
     * @param $config
     * @param $userLoggedIn
     *
     * @return bool
     */
    private function showPrices($configShowPrices, $config, $userLoggedIn)
    {
        $showPrices = false;

        if ($configShowPrices === 0) { // 0 -> hide prices
            $showPrices = false;
        }

        if ($configShowPrices === 1) { // 1 -> show prices
            $showPrices = true;
        }

        if ($configShowPrices === 2) { // 2 -> show prices only for valid logged-in customer groups
            $userCustomerGroup = $this->container->get('system')->sUSERGROUP;
            $validCustomerGroups = $config['show_group'];

            if ($userLoggedIn && in_array($userCustomerGroup, $validCustomerGroups)) {
                $showPrices = true;
            }
        }

        return $showPrices;
    }
}
