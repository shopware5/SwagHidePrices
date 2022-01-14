<?php
declare(strict_types=1);
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

namespace SwagHidePrices\Tests;

trait CustomerLoginTrait
{
    public function loginCustomer(
        string $sessionId = 'phpUnitTestSession',
        int $customerId = 1,
        string $password = 'a256a310bc1e5db755fd392c524028a8',
        ?string $passwordChangeDate = null,
        string $email = 'test@example.com',
        int $countryId = 2,
        int $areaId = 3,
        ?int $stateId = null,
        string $customerGroupKey = 'EK'
    ): void {
        Shopware()->Container()->reset('modules');
        $session = Shopware()->Container()->get('session');

        if ($passwordChangeDate === null) {
            $passwordChangeDate = Shopware()->Container()->get('dbal_connection')->fetchColumn(
                'SELECT `password_change_date` FROM `s_user` WHERE `id` = :customerId;',
                [
                    'customerId' => $customerId,
                ]
            );
        }

        $session->offsetSet('sessionId', $sessionId);
        $session->offsetSet('sUserId', $customerId);
        $session->offsetSet('sUserMail', $email);
        $session->offsetSet('sUserPassword', $password);
        $session->offsetSet('sUserPasswordChangeDate', $passwordChangeDate);
        $session->offsetSet('sCountry', $countryId);
        $session->offsetSet('sArea', $areaId);
        $session->offsetSet('sUserGroup', $customerGroupKey);
        $session->offsetSet('sState', $stateId);

        Shopware()->Container()->get('dbal_connection')->executeQuery(
            'UPDATE s_user SET sessionID = :sessionId, lastlogin = now() WHERE id=:customerId',
            [
                ':sessionId' => $sessionId,
                ':customerId' => $customerId,
            ]
        );

        static::assertTrue(Shopware()->Modules()->Admin()->sCheckUser());
    }

    public function logoutCustomer(): void
    {
        $session = Shopware()->Container()->get('session');
        $session->offsetUnset('sessionId');
        $session->offsetUnset('sUserId');
        $session->offsetUnset('sUserMail');
        $session->offsetUnset('sUserPassword');
        $session->offsetUnset('sUserPasswordChangeDate');
        $session->offsetUnset('sUserGroup');
        $session->offsetUnset('sCountry');
        $session->offsetUnset('sArea');
        $session->offsetUnset('sState');

        static::assertFalse(Shopware()->Modules()->Admin()->sCheckUser());
    }
}
