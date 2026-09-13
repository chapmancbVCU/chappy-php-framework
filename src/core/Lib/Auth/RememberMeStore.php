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
 *
 * Security invariant: the raw token is placed only in the user's cookie;
 * the user_sessions row stores only its hash (see RememberToken).  The two
 * are reconciled at auto-login time by hashing the incoming cookie value
 * and matching it against the stored hash, so a leaked user_sessions table
 * never exposes usable tokens.  Any change here must preserve that split —
 * never store the raw token, and never put the hash in the cookie.
 *
 * The store is keyed by user id plus user agent, so each device holds an
 * independent token; persisting for a device first clears that device's
 * prior row.
 */
final class RememberMeStore {
    /**
     * Removes the persisted token row and cookie for the current request.
     * Mirrors the remember-me half of the prior logoutUser().  Safe to call
     * when no remember-me cookie or row exists — both are checked first.
     *
     * @return void
     */
    public function forget(): void {
        $userSession = UserSessions::getFromCookie();
        if($userSession) $userSession->delete();

        if(Cookie::exists(env('REMEMBER_ME_COOKIE_NAME'))) {
            Cookie::delete(env('REMEMBER_ME_COOKIE_NAME'));
        }
    }

    /**
     * Persists a remember-me token for the user: the raw token is written
     * to the cookie, and only its hash is stored in user_sessions.  Any
     * existing row for this user and user agent is removed first, so a
     * device holds a single current token.  Mirrors the prior loginUser()
     * block.
     *
     * @param Principal $user The user to persist a remember-me token for.
     * @return void
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