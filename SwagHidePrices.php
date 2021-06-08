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

use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\ActivateContext;
use Shopware\Components\Plugin\Context\InstallContext;

class SwagHidePrices extends Plugin
{
    public const NO_CACHE_CONTROLLERS_KEY = 'noCacheControllers';

    public const DO_NOT_CACHE_PRICE_TAG = 'frontend/detail price';

    public function install(InstallContext $context)
    {
        $this->installNoCacheTag();
    }

    public function activate(ActivateContext $context)
    {
        $context->scheduleClearCache(ActivateContext::CACHE_LIST_DEFAULT);
    }

    private function installNoCacheTag(): void
    {
        $configWriter = $this->container->get('config_writer');
        $configValue = $configWriter->get(self::NO_CACHE_CONTROLLERS_KEY);

        $configValueArray = \explode(\PHP_EOL, $configValue);
        if ($this->hasTag($configValueArray)) {
            return;
        }

        $configValueArray[] = self::DO_NOT_CACHE_PRICE_TAG;
        $configValue = \implode(\PHP_EOL, $configValueArray);

        $configWriter->save(self::NO_CACHE_CONTROLLERS_KEY, $configValue);
    }

    private function hasTag(array $configValueArray): bool
    {
        foreach ($configValueArray as $value) {
            if ($value === self::DO_NOT_CACHE_PRICE_TAG) {
                return true;
            }
        }

        return false;
    }
}
