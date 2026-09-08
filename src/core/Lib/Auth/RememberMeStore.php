<?php
declare(strict_types=1);
namespace Core\Lib\Auth;

use Core\Cookie;
use Core\DB;
use Core\Lib\Contracts\Principal;
use Core\Models\UserSessions;
use Core\Session;

/**
 * Owns remember-me persistence: minting the cookie token and its hashed
 * user_sessions row, and tearing both down on logout.
 */
final class RememberMeStore {
    /**
     * Remove the persisted token row and cookie.  Mirrors the remember-me
     * half of the prior logoutUser().
     */
    public function forget(): void {
        $userSession = UserSessions::getFromCookie();
        if($userSession) $userSession->delete();

        if(Cookie::exists(env('REMEMBER_ME_COOKIE_NAME'))) {
            Cookie::delete(env('REMEMBER_ME_COOKIE_NAME'));
        }
    }

    /**
     * Persist a remember-me token for the user: raw token to the cookie,
     * its hash to user_sessions.  Mirrors the prior loginUser() block.
     */
    public function persist(Principal $user): void {
        $token = RememberToken::generate();
        $storedHash = RememberToken::hash($token);
        $userAgent = Session::uagent_no_version();
        $userId = $user->getAuthIdentifier();

        Cookie::set(
            env('REMEMBER_ME_COOKIE_NAME'),
            $token,
            (int)env('REMEMBER_ME_COOKIE_EXPIRY', 2592000)
        );

        DB::getInstance()->query(
            "DELETE FROM user_sessions WHERE user_id = ? AND user_agent = ?",
            [$userId, $userAgent]
        );

        $us = new UserSessions();
        $us->assign(['session' => $storedHash, 'user_agent' => $userAgent, 'user_id' => $userId]);
        $us->save();
    }
}