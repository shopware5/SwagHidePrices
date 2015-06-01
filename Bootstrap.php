<?php
/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

/**
 * Shopware SwagHidePrices Plugin - Bootstrap
 *
 * @category  Shopware
 * @package   Shopware\Plugins\SwagHidePrices
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 */
class Shopware_Plugins_Frontend_SwagHidePrices_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    /**
     * Install routine
     *
     * @return bool
     */
    public function install()
    {
        $this->createMyEvents();

        $form = $this->Form();
        $form->setElement('text', 'show_group', array(
            'label' => 'Preisanzeige nur für Kundengruppe (Semikolon getrennt)',
            'value' => 'EK',
            'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));
        $form->setElement('select', 'show_prices', array(
            'label' => 'Preise anzeigen',
            'value' => 1,
            'store' => array(array(1, 'Ja'), array(0, 'Nein'), array(2, 'Nur für Registrierte')),
            'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));

        return true;
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
            'description' => file_get_contents($this->Path() . 'info.txt'),
            'link' => 'http://www.shopware.de/',
            'changes' => array(
                '1.0.0'=>array('releasedate'=>'2011-09-16', 'lines' => array(
                    'First release'
                )),
                '1.0.2'=>array('releasedate'=>'2012-10-15', 'lines' => array(
                    'Updated for Shopware 4.0'
                )),
                '1.0.3'=>array('releasedate'=>'2012-11-08', 'lines' => array(
                    'Fixed a model bug, so you can install the plugin'
                )),
                '1.0.4'=>array('releasedate'=>'2012-12-03', 'lines' => array(
                    'Make sure that smarty_modifier_currency is available'
                )),
                '1.0.5'=>array('releasedate'=>'2012-12-03', 'lines' => array(
                    'Add support for http cache'
                ))
            )
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
        $info = json_decode(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR .'plugin.json'), true);

        if ($info) {
            return $info['currentVersion'];
        } else {
            throw new Exception('The plugin has an invalid version file.');
        }
    }

    protected static $showPrices = true;

    /**
     * Sets $showPrice depending on customer group and current plugin settings,
     * extends template and loads smarty_modifier_currency plugin
     * @param Enlight_Event_EventArgs $args
     */
    public function onPostDispatch(Enlight_Event_EventArgs $args)
    {
        $request = $args->getSubject()->Request();
        $response = $args->getSubject()->Response();
        $view = $args->getSubject()->View();

	    if(
			    $request->getModuleName() != 'frontend' &&
			    $request->getModuleName() != 'widgets'
	    ) {
		    return;
	    }

        $config = $this->Config();
        $userLoggedIn = (bool)Shopware()->Session()->sUserId;
        $userCustomerGroup = Shopware()->System()->sUSERGROUP;
        $customerGroup = explode(";", $config->show_group);
        $configShowPrices = $config->show_prices;

        if($request->getControllerName() == "account") {
            $showPrices = true;
        } elseif(!empty($configShowPrices) && ($configShowPrices == 1 || $userLoggedIn)) {
            $showPrices = true;
        } elseif(in_array($userCustomerGroup, $customerGroup)) {
            $showPrices = true;
        } else {
            $showPrices = false;
        }

        /** @var Shopware_Plugins_Core_HttpCache_Bootstrap $httpCache */
        $httpCache = Shopware()->Plugins()->Core()->get('HttpCache');
        if($httpCache !== null && $this->checkIfHttpCacheIsActive()) {
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
        $view->extendsTemplate('frontend/plugins/swag_hide_prices/index.tpl');
    }

    /**
     * Modify currency callback function
     * Returns price or empty string depending on the showPrice setting
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

    private function checkIfHttpCacheIsActive()
    {
        $sql = "SELECT `active` FROM `s_core_plugins` WHERE `name` = 'HttpCache' LIMIT 1";
        /** @var Enlight_Components_Db_Adapter_Pdo_Mysql $db */
        $db = $this->get('db');
        $result = (int)$db->fetchOne($sql);
        if($result === 1){
            return true;
        }
        return false;
    }

	public function update($version)
	{
		$this->createMyEvents();

		return true;
	}

	private function createMyEvents()
	{
		$this->subscribeEvent('Enlight_Controller_Action_PostDispatchSecure', 'onPostDispatch');
	}
}