<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagHidePrices;

use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\ActivateContext;
use Shopware\Components\Plugin\Context\InstallContext;

class SwagHidePrices extends Plugin
{
    public const NO_CACHE_CONTROLLERS_KEY = 'noCacheControllers';

    public const DO_NOT_CACHE_PRICE_TAG = 'frontend/detail price';

    public function install(InstallContext $context): void
    {
        $this->installNoCacheTag();
    }

    public function activate(ActivateContext $context): void
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

    /**
     * @param string[] $configValueArray
     */
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
