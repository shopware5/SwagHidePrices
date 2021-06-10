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

return [
    'db' => [
        'username' => 'root',
        'password' => 'root',
        'dbname' => 'shopware',
        'host' => 'mysql',
        'port' => '3306',
    ],
    'front' => [
        'throwExceptions' => true,
    ],

    'cache' => [
        'backend' => 'Black-Hole',
        'backendOptions' => [],
        'frontendOptions' => [
            'write_control' => false,
        ],
    ],

    'model' => [
        'cacheProvider' => 'Array',
    ],

    'httpCache' => [
        'enabled' => true,
        'debug' => true,
    ],

    'phpsettings' => [
        'display_errors' => 1,
        'display_startup_errors' => 1,
    ],

    'csrfProtection' => [
        'frontend' => false,
        'backend' => false,
    ],

    'session' => [
        'unitTestEnabled' => true,
        'name' => 'SHOPWARESID',
        'cookie_lifetime' => 0,
        'use_trans_sid' => false,
        'gc_probability' => 1,
        'gc_divisor' => 100,
        'save_handler' => 'db',
    ],
];
