<h1>Welcome, <?=e($user->fname)?></h1>
<p>Thank you for registering with <?=e(env('SITE_TITLE'))?>.  Access your account by clicking <a href="<?=route('auth.login')?>">here</a>
</p>
<p>Your username is <?=e($user->username)?></p>
