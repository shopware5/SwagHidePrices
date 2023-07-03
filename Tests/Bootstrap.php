<?php
declare(strict_types=1);
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Shopware\Kernel;
use Shopware\Models\Shop\Repository;
use Shopware\Models\Shop\Shop;

require_once __DIR__ . '/../../../../autoload.php';

$enlightLoader = new Enlight_Loader();

$enlightLoader->registerNamespace(
    'SwagHidePrices',
    __DIR__ . '/../'
);

class SwagHidePricesTestKernel extends Kernel
{
    public static function start(): void
    {
        $kernel = new self((string) getenv('SHOPWARE_ENV') ?: 'testing', true);
        $kernel->boot();

        $container = $kernel->getContainer();
        $container->get('plugins')->Core()->ErrorHandler()->registerErrorHandler(\E_ALL | \E_STRICT);

        /** @var Repository $repository */
        $repository = $container->get('models')->getRepository(Shop::class);

        $shop = $repository->getActiveDefault();
        $shopRegistrationService = $container->get('shopware.components.shop_registration_service');
        $shopRegistrationService->registerResources($shop);

        $_SERVER['HTTP_HOST'] = $shop->getHost();

        if (!self::assertPlugin()) {
            throw new Exception('Plugin SwagHidePrices is not installed or activated.');
        }
    }

    private static function assertPlugin(): bool
    {
        $sql = 'SELECT 1 FROM s_core_plugins WHERE name = ? AND active = 1';

        return (bool) Shopware()->Container()->get('dbal_connection')->fetchColumn($sql, ['SwagHidePrices']);
    }
}

SwagHidePricesTestKernel::start();
