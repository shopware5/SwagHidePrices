<?php
declare(strict_types=1);
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
