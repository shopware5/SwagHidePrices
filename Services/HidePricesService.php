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

namespace SwagHidePrices\Services;

use Doctrine\DBAL\Connection;
use Shopware\Components\Plugin\CachedConfigReader;
use Shopware\Components\Plugin\Configuration\CachedReader;

class HidePricesService implements HidePricesServiceInterface
{
    const SHOW_PRICES_LEVEL_CONFIG_KEY = 'show_prices';

    const CUSTOMER_GROUPS_CONFIG_KEY = 'show_group';

    const HTTP_CACHE_PLUGIN_NAME = 'HttpCache';

    const PRICE_CACHE_TAG = 'price';

    const PRICE_LEVEL_HIDE_PRICES = 0;

    const PRICE_LEVEL_SHOW_PRICES = 1;

    const PRICE_LEVEL_SHOW_FOR_VALID_CUSTOMER_GROUPS = 2;

    /**
     * @var bool
     */
    private $isLegacyConfigReader;

    /**
     * @var CachedReader|CachedConfigReader
     */
    private $configReader;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var DependencyProviderInterface
     */
    private $dependencyProvider;

    /**
     * @var string
     */
    private $pluginName;

    /**
     * @var \Enlight_Plugin_PluginCollection
     */
    private $plugins;

    public function __construct(
        Connection $connection,
        DependencyProviderInterface $dependencyProvider,
        string $pluginName,
        \Enlight_Plugin_PluginCollection $plugins,
        ?CachedReader $cachedReader,
        ?CachedConfigReader $legacyConfigReader
    ) {
        $this->connection = $connection;
        $this->dependencyProvider = $dependencyProvider;
        $this->pluginName = $pluginName;
        $this->plugins = $plugins;
        $this->setConfigReader($cachedReader, $legacyConfigReader);
    }

    public function shouldShowPrices(): bool
    {
        $pluginConfig = $this->getPluginConfig();
        $customerGroups = $this->getValidCustomerGroups($pluginConfig);
        $showPricesLevel = $pluginConfig[self::SHOW_PRICES_LEVEL_CONFIG_KEY];
        $isUserLoggedIn = $this->isUserLoggedIn();

        $this->setPriceNoCacheTag($showPricesLevel, $isUserLoggedIn);

        return $this->showPrices($showPricesLevel, $customerGroups, $isUserLoggedIn);
    }

    private function setConfigReader(?CachedReader $cachedReader, ?CachedConfigReader $legacyConfigReader)
    {
        if ($cachedReader !== null) {
            $this->configReader = $cachedReader;
            $this->isLegacyConfigReader = false;

            return;
        }

        $this->configReader = $legacyConfigReader;
        $this->isLegacyConfigReader = true;
    }

    private function showPrices(int $showPricesLevel, array $validCustomerGroups, bool $userLoggedIn): bool
    {
        if ($showPricesLevel === self::PRICE_LEVEL_HIDE_PRICES) {
            return false;
        }

        if ($showPricesLevel === self::PRICE_LEVEL_SHOW_PRICES) {
            return true;
        }

        if ($showPricesLevel === self::PRICE_LEVEL_SHOW_FOR_VALID_CUSTOMER_GROUPS) {
            $userCustomerGroup = $this->dependencyProvider->getCurrentUserUserGroup();

            if ($userLoggedIn && \in_array($userCustomerGroup, $validCustomerGroups)) {
                return true;
            }
        }

        return false;
    }

    private function setPriceNoCacheTag(int $showPricesLevel, bool $userLoggedIn): void
    {
        $httpCache = $this->plugins->Core()->get(self::HTTP_CACHE_PLUGIN_NAME);
        $httpCacheIsActive = (bool) $httpCache->Info()->get('active');

        if ($httpCache !== null && $showPricesLevel === 2 && $userLoggedIn && $httpCacheIsActive) {
            $httpCache->setNoCacheTag(self::PRICE_CACHE_TAG);
        }
    }

    private function getPluginConfig(): array
    {
        if ($this->isLegacyConfigReader) {
            return $this->configReader->getByPluginName($this->pluginName, $this->dependencyProvider->getShop());
        }

        return $this->configReader->getByPluginName($this->pluginName, $this->dependencyProvider->getShop()->getId());
    }

    private function isUserLoggedIn(): bool
    {
        return (bool) $this->dependencyProvider->getSession()->offsetGet('sUserId');
    }

    private function getValidCustomerGroups(array $config): array
    {
        $validCustomerGroups = $config[self::CUSTOMER_GROUPS_CONFIG_KEY];

        if (!\is_array($validCustomerGroups)) {
            $validCustomerGroups = $this->normalizeCustomerGroups($validCustomerGroups);
        }

        if (\count($validCustomerGroups) === 1 && !$this->isValidCustomerGroup($validCustomerGroups[0])) {
            $validCustomerGroups = $this->normalizeCustomerGroups($validCustomerGroups[0]);
        }

        return $validCustomerGroups;
    }

    private function normalizeCustomerGroups(string $customerGroups): array
    {
        return \array_map('trim', \explode(',', $customerGroups));
    }

    private function isValidCustomerGroup(string $customerGroupKey): bool
    {
        return (bool) $this->connection->createQueryBuilder()->select(['id'])
            ->from('s_core_customergroups')
            ->where('groupkey LIKE :groupKey')
            ->setParameter('groupKey', $customerGroupKey)
            ->execute()
            ->fetch(\PDO::FETCH_COLUMN);
    }
}
