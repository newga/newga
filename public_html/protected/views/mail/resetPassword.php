<html>
<head>
<title>Your password reset request</title>
</head>
<body>
<?php print Yii::app()->params['emailStyles']; ?>
<h1>Your password reset request</h1>
<p>Hello <?php print $name; ?>,</p>
<p>You have requested to reset your password on <?php print Yii::app()->name; ?>,<br />
please follow the link below to confirm this request and choose a new password.</p>
<p><?php print $resetLink; ?></p>
</body>
</html>