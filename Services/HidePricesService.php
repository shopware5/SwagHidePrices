<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagHidePrices\Services;

use Doctrine\DBAL\Connection;
use Shopware\Components\Plugin\CachedConfigReader;
use Shopware\Components\Plugin\Configuration\CachedReader;

class HidePricesService implements HidePricesServiceInterface
{
    public const SHOW_PRICES_LEVEL_CONFIG_KEY = 'show_prices';

    public const CUSTOMER_GROUPS_CONFIG_KEY = 'show_group';

    /**
     * @deprecated - Will be removed in 3.0.0 without replacement
     */
    public const HTTP_CACHE_PLUGIN_NAME = 'HttpCache';

    public const PRICE_CACHE_TAG = 'price';

    public const PRICE_LEVEL_HIDE_PRICES = 0;

    public const PRICE_LEVEL_SHOW_PRICES = 1;

    public const PRICE_LEVEL_SHOW_FOR_VALID_CUSTOMER_GROUPS = 2;

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
     * @var \Enlight_Plugin_PluginManager
     */
    private $plugins;

    public function __construct(
        Connection $connection,
        DependencyProviderInterface $dependencyProvider,
        string $pluginName,
        \Enlight_Plugin_PluginManager $plugins,
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

    private function setConfigReader(?CachedReader $cachedReader, ?CachedConfigReader $legacyConfigReader): void
    {
        if ($cachedReader !== null) {
            $this->configReader = $cachedReader;

            return;
        }

        if ($legacyConfigReader === null) {
            throw new \RuntimeException('No config reader given');
        }

        $this->configReader = $legacyConfigReader;
    }

    /**
     * @param string[] $validCustomerGroups
     */
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
        $httpCache = $this->plugins->Core()->HttpCache();
        $httpCacheIsActive = (bool) $httpCache->Info()->get('active');

        if ($showPricesLevel === self::PRICE_LEVEL_SHOW_FOR_VALID_CUSTOMER_GROUPS
            && $userLoggedIn
            && $httpCacheIsActive
        ) {
            $httpCache->setNoCacheTag(self::PRICE_CACHE_TAG);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function getPluginConfig(): array
    {
        if ($this->configReader instanceof CachedConfigReader) {
            return $this->configReader->getByPluginName($this->pluginName, $this->dependencyProvider->getShop());
        }

        return $this->configReader->getByPluginName($this->pluginName, $this->dependencyProvider->getShop()->getId());
    }

    private function isUserLoggedIn(): bool
    {
        return (bool) $this->dependencyProvider->getSession()->offsetGet('sUserId');
    }

    /**
     * @param array<string, mixed> $config
     *
     * @return string[]
     */
    private function getValidCustomerGroups(array $config): array
    {
        $validCustomerGroups = $config[self::CUSTOMER_GROUPS_CONFIG_KEY];

        if ($validCustomerGroups === null) {
            return [];
        }

        if (!\is_array($validCustomerGroups)) {
            $validCustomerGroups = $this->normalizeCustomerGroups($validCustomerGroups);
        }

        if (\count($validCustomerGroups) === 1 && !$this->isValidCustomerGroup($validCustomerGroups[0])) {
            $validCustomerGroups = $this->normalizeCustomerGroups($validCustomerGroups[0]);
        }

        return $validCustomerGroups;
    }

    /**
     * @return string[]
     */
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
