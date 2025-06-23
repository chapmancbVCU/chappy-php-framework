<h1>Reset password for <?=e($user->username)?></h1>
<p>
    An event occurred that requires you to reset your password.  
    You can reset your password <a href="<?=route('auth.resetPassword', [$user->id])?>">here</a>
</p>
