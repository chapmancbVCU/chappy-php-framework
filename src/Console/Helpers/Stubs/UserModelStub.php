<?php
declare(strict_types=1);
namespace Console\Helpers\Stubs;

use Core\Lib\Auth\Auth;

/**
 * Supplies the application's user model to code generators.  Kit stubs
 * (auth, profile, admin dashboard, events, notifications) generate code
 * that lives in the application, so they reference the application's
 * concrete user model rather than resolving it at runtime.  The class is
 * read from auth.user_model at generation time so generated code matches
 * whatever the application has configured.
 */
class UserModelStub {
    /**
     * The fully qualified class name of the configured user model, for use
     * in a generated use statement.
     *
     * @return string For example App\Models\Users.
     */
    public static function fqcn(): string {
        return ltrim(Auth::userModel(), '\\');
    }

    /**
     * The short class name of the configured user model, for use in
     * generated type hints and static calls.
     *
     * @return string For example Users.
     */
    public static function shortName(): string {
        $fqcn = self::fqcn();
        $pos = strrpos($fqcn, '\\');
        return $pos === false ? $fqcn : substr($fqcn, $pos + 1);
    }
}
