<?php
return array(
    'version' => $this->getVersion(),
	'label' => $this->getLabel(),
	'description' => 'This plugin disables the displaying of article prices.',
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
        ))
	)
);