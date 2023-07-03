<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagHidePrices\Services;

use Shopware\Components\DependencyInjection\Container;
use Shopware\Models\Shop\Shop;

class DependencyProvider implements DependencyProviderInterface
{
    /**
     * @var Container
     */
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function getSession(): \Enlight_Components_Session_Namespace
    {
        return $this->container->get('session');
    }

    public function getShop(): Shop
    {
        $shop = $this->container->get('shop');
        if (!$shop instanceof Shop) {
            throw new \RuntimeException('Shop not set in DI container');
        }

        return $shop;
    }

    public function getCurrentUserUserGroup(): string
    {
        /** @var \Shopware_Components_Modules $modules */
        $modules = $this->container->get('modules');

        return $modules->System()->sUSERGROUP;
    }
}
