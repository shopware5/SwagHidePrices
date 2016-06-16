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

/**
 * Shopware SwagHidePrices Plugin - Bootstrap
 *
 * @category  Shopware
 * @package   Shopware\Plugins\SwagHidePrices
 * @copyright Copyright (c) 2016, shopware AG (http://de.shopware.com)
 */
class Shopware_Plugins_Frontend_SwagHidePrices_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    protected static $showPrices = true;

    /**
     * Install routine
     *
     * @return bool
     */
    public function install()
    {
        if (!$this->assertMinimumVersion('5.2.0')) {
            throw new \RuntimeException('At least Shopware 5.2.0 is required');
        }

        $this->createMyEvents();

        $this->createMyForm();

        return true;
    }

    /**
     * Standard plugin enable method
     *
     * @return array
     */
    public function enable()
    {
        return array('success' => true, 'invalidateCache' => array('frontend'));
    }

    /**
     * Standard plugin disable method
     *
     * @return array
     */
    public function disable()
    {
        return array('success' => true, 'invalidateCache' => array('frontend'));
    }

    /**
     * Returns the well-formatted name of the plugin
     * as a sting
     *
     * @return string
     */
    public function getLabel()
    {
        return 'Keine Preise ohne Login';
    }

    /**
     * Returns the meta information about the plugin
     * as an array.
     * Keep in mind that the plugin description located
     * in the info.txt.
     *
     * @return array
     */
    public function getInfo()
    {
        return array(
            'version' => $this->getVersion(),
            'label' => $this->getLabel(),
            'link' => 'http://de.shopware.com/'
        );
    }

    /**
     * Returns the version of the plugin as a string
     *
     * @return mixed
     * @throws Exception
     */
    public function getVersion()
    {
        $info = json_decode(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'plugin.json'), true);

        if ($info) {
            return $info['currentVersion'];
        } else {
            throw new Exception('The plugin has an invalid version file.');
        }
    }

    /**
     * Sets $showPrice depending on customer group and current plugin settings,
     * extends template and loads smarty_modifier_currency plugin
     *
     * @param Enlight_Event_EventArgs $args
     */
    public function onPostDispatch(Enlight_Event_EventArgs $args)
    {
        /** @var Enlight_Controller_Action $subject */
        $subject = $args->getSubject();

        /** @var Enlight_Controller_Request_RequestHttp $request */
        $request = $subject->Request();

        /** @var Enlight_View_Default $view */
        $view = $subject->View();

        if ($request->getModuleName() != 'frontend' && $request->getModuleName() != 'widgets') {
            return;
        }

        $config = $this->Config();
        $userLoggedIn = (bool) Shopware()->Session()->sUserId;
        $userCustomerGroup = Shopware()->System()->sUSERGROUP;
        $validCustomerGroups = explode(";", $config->show_group);
        $configShowPrices = $config->show_prices;

        $showPrices = false;

        if ($configShowPrices == 0) { // 0 -> hide prices
            $showPrices = false;
        }

        if ($configShowPrices == 1) { // 1 -> show prices
            $showPrices = true;
        }

        if ($configShowPrices == 2) { // 2 -> show prices only for valid logged-in customer groups
            if ($userLoggedIn && in_array($userCustomerGroup, $validCustomerGroups)) {
                $showPrices = true;
            }
        }

        /** @var Shopware_Plugins_Core_HttpCache_Bootstrap $httpCache */
        $httpCache = Shopware()->Plugins()->Core()->get('HttpCache');
        if ($httpCache !== null && $this->checkIfHttpCacheIsActive()) {
            if ($configShowPrices == 2 && $userLoggedIn) {
                $httpCache->setNoCacheTag('price');
            }
        }

        self::$showPrices = $showPrices;
        $view->ShowPrices = $showPrices;

        /** @var $engine Enlight_Template_Manager */
        $engine = $view->Engine();

        $engine->unregisterPlugin('modifier', 'currency');
        $engine->registerPlugin('modifier', 'currency', __CLASS__ . '::modifierCurrency');
        $engine->loadPlugin('smarty_modifier_currency');

        $view->addTemplateDir($this->Path() . 'Views/');
    }

    /**
     * Modify currency callback function
     * Returns price or empty string depending on the showPrice setting
     *
     * @param      $value
     * @param null $config
     * @param null $position
     * @return float|string
     */
    public static function modifierCurrency($value, $config = null, $position = null)
    {
        if (!self::$showPrices) {
            return '';
        }

        if (!function_exists('smarty_modifier_currency')) {
            return number_format($value, 2, ',', '');
        }

        return smarty_modifier_currency($value, $config, $position);
    }

    /**
     * @return bool
     */
    private function checkIfHttpCacheIsActive()
    {
        $sql = "SELECT `active` FROM `s_core_plugins` WHERE `name` = 'HttpCache' LIMIT 1";
        /** @var Enlight_Components_Db_Adapter_Pdo_Mysql $db */
        $db = $this->get('db');
        $result = (int) $db->fetchOne($sql);
        if ($result === 1) {
            return true;
        }

        return false;
    }

    /**
     * @param string $version
     * @return bool
     */
    public function update($version)
    {
        if (!$this->assertMinimumVersion('5.2.0')) {
            throw new \RuntimeException('At least Shopware 5.2.0 is required');
        }

        $this->createMyEvents();

        $this->createMyForm();

        return true;
    }

    /**
     * subscribes events
     */
    private function createMyEvents()
    {
        $this->subscribeEvent('Enlight_Controller_Action_PostDispatchSecure_Frontend', 'onPostDispatch');
        $this->subscribeEvent('Enlight_Controller_Action_PostDispatchSecure_Widgets', 'onPostDispatch');
    }

    /**
     * creates plugin config form
     */
    private function createMyForm()
    {
        $form = $this->Form();

        $form->setElement(
            'select',
            'show_prices',
            array(
                'label' => 'Preise anzeigen',
                'value' => 1,
                'store' => array(
                    array(1, 'Ja'),
                    array(0, 'Nein'),
                    array(2, 'Nur für Kundengruppen, die im unteren Feld defniert werden')
                ),
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'description' => 'Diese Option wirkt global: <br><i>Ja</i> - Preise immer anzeigen (unabhängig von Kundengruppen)<br><i>Nein</i> - Preise immer verbergen (unabhängig von Kundengruppen)<br><i>Nur für Kundengruppen, die im unteren Feld defniert werden</i> - Preise werden angezeigt oder verborgen abhängig von den genannten Kundengruppen.',

            )
        );

        $form->setElement(
            'text',
            'show_group',
            array(
                'label' => 'Preisanzeige nur für Kundengruppe (Semikolon getrennt)',
                'value' => 'EK',
                'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP,
                'description' => 'Geben Sie hier die Kundengruppen an, für die Preise angezeigt werden sollen. Mehrere Kundengruppen werden durch ein Semikolon getrennt. Diese Einstellung wirkt nur dann, wenn im oberen Feld <i>Nur für Kundengruppen, die im unteren Feld defniert werden</i> ausgewählt ist.'
            )
        );

        $this->addFormTranslations(
            array(
                'en_GB' => array(
                    'show_prices' => array(
                        'label' => 'Show Prices',
                        'description' => 'This Option has global effect: <br><i>Yes</i> - Always show prices (independent of customer groups)<br><i>No</i> - Always hide prices (independent of customer groups)<br><i>Only for customer group defined in the lower field</i> - Prices get shown or hidden depending on specified customer groups.',
                        'store' => array(
                            array(1, 'Yes'),
                            array(0, 'No'),
                            array(2, 'Only for customer group defined in the lower field')
                        )
                    ),
                    'show_group' => array(
                        'label' => 'Show Prices only for customer groups',
                        'description' => 'Enter customer groups that are allowed to see prices. Multiple customer groups must be separated by semicolon. This option only has an effect if <i>Only for customer group defined in the lower field</i> is selected in the upper field.'
                    )
                )
            )
        );
    }
}