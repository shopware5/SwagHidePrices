<?php

class Shopware_Plugins_Frontend_SwagHidePrices_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{

	public function install()
	{
		$this->subscribeEvent('Enlight_Controller_Action_PostDispatch', 'onPostDispatch');

		$form = $this->Form();
		$form->setElement('text', 'show_group', array('label' => 'Preisanzeige nur für Kundengruppe (Semikolon getrennt)', 'value' => 'EK', 'scope' => Shopware_Components_Form::SCOPE_SHOP));
		$form->setElement('select', 'show_prices', array('label' => 'Preise anzeigen', 'value' => 1, 'store' => array(array(1, 'Ja'), array(0, 'Nein'), array(2, 'Nur für Registrierte')), 'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP));

		return true;
	}

	public function getInfo()
	{
		return include($this->Path().'Meta.php');
	}

	protected static $showPrices = true;

	public function onPostDispatch(Enlight_Event_EventArgs $args)
	{
		$request = $args->getSubject()->Request();
		$response = $args->getSubject()->Response();
		$view = $args->getSubject()->View();

		if (!$request->isDispatched() || $response->isException() || $request->getModuleName() != 'frontend' || $request->getControllerName() == "account") {
			return;
		}

		$config = $this->Config();
		$userLoggedIn = Shopware()->Modules()->Admin()->sCheckUser();
		$userCustomerGroup = Shopware()->System()->sUSERGROUPDATA['groupkey'];
		$customerGroup = explode(";", $config->show_group);
		$showPrices = !empty($config->show_prices) && ($config->show_prices == 1 || $userLoggedIn) || in_array($userCustomerGroup, $customerGroup);
		self::$showPrices = $showPrices;

		$view->ShowPrices = $showPrices;

		$view->Engine()->unregisterPlugin('modifier', 'currency');
		$view->Engine()->registerPlugin('modifier', 'currency', __CLASS__ . '::modifierCurrency');
		$view->Engine()->loadPlugin('smarty_modifier_currency');
		$view->extendsBlock('frontend_index_header_css_screen', '
			{if !$ShowPrices}
				<style type="text/css">
				.price { display:none !important }
				.pseudo { display:none !important }
				.article_details_price2 { display:none !important }
				.article_details_price { display:none !important }
				</style>
			{/if}', 'append');
		$view->addTemplateDir($this->Path() . 'Views/');
		$view->extendsTemplate('frontend/plugins/swag_hide_prices/index.tpl');
	}

	public function modifierCurrency($value, $config = null, $position = null)
	{
		if (!self::$showPrices) {
			return '';
		}
		return smarty_modifier_currency($value, $config, $position);
	}
}