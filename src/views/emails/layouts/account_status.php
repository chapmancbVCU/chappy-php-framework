<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: sans-serif; }
        .email-wrapper { padding: 20px; border: 1px solid #eee; }
        .center { text-align: center; }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <h1 class="center">Account Status</h1>
        <?= $content ?>
        <hr>
        <p>This E-mail was auto generated and may not be monitored.  If you are not the intended recipient contact the site administrator and delete this E-mail.</p>
    </div>
</body>
</html>