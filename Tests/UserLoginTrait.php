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

namespace SwagHidePrices\Tests;

trait UserLoginTrait
{
    public function loginUser(
        string $sessionId = 'phpUnitTestSession',
        int $userId = 1,
        string $email = 'test@example.com',
        string $password = 'a256a310bc1e5db755fd392c524028a8',
        string $userGroup = 'EK',
        int $countryId = 2,
        int $areaId = 3,
        int $stateId = null
    ): bool {
        $session = Shopware()->Container()->get('session');
        $session->offsetSet('sessionId', $sessionId);
        $session->offsetSet('sUserId', $userId);
        $session->offsetSet('sUserMail', $email);
        $session->offsetSet('sUserPassword', $password);
        $session->offsetSet('sUserGroup', $userGroup);
        $session->offsetSet('sCountry', $countryId);
        $session->offsetSet('sArea', $areaId);
        $session->offsetSet('sState', $stateId);

        Shopware()->Container()->get('dbal_connection')->executeQuery(
            'UPDATE s_user SET sessionID = :sessionId, lastlogin = now() WHERE id=:userId',
            [
                ':sessionId' => $sessionId,
                ':userId' => $userId,
            ]
        );

        Shopware()->Container()->get('system')->sUSERGROUP = $userGroup;

        return Shopware()->Modules()->Admin()->sCheckUser();
    }

    public function logOutUser(): bool
    {
        $session = Shopware()->Container()->get('session');
        $session->offsetUnset('sessionId');
        $session->offsetUnset('sUserId');
        $session->offsetUnset('sUserMail');
        $session->offsetUnset('sUserPassword');
        $session->offsetUnset('sUserGroup');
        $session->offsetUnset('sCountry');
        $session->offsetUnset('sArea');
        $session->offsetUnset('sState');

        return !Shopware()->Modules()->Admin()->sCheckUser();
    }
}
