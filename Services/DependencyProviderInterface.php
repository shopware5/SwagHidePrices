<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagHidePrices\Services;

use Shopware\Models\Shop\Shop;

interface DependencyProviderInterface
{
    public function getSession(): \Enlight_Components_Session_Namespace;

    public function getShop(): Shop;

    public function getCurrentUserUserGroup(): string;
}
