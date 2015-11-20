<html>
<head>
<title>Welcome</title>
</head>
<body>
<?php print Yii::app()->params['emailStyles']; ?>
<h1>Welcome</h1>
<p>Hello <?php print $name; ?>,</p>
<p>You have been given access to <?php print Yii::app()->name; ?>,<br />
Please follow the link below to confirm and choose a new password.</p>
<p><?php print $resetLink; ?></p>
</body>
</html>