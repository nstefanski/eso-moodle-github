<?php ?>
<head>
	<title>My Escoffier Login</title>
	<link rel='shortcut icon' type='image/x-icon' href='my.escoffieronline.com/theme/essential/pix/favicon.ico' />
</head>
<body>
	<div style="display: inline-block;">
		<form name = "moodle" action="http://my.escoffieronline.com/login/index.php" enctype=x-www-form-urlencoded method="POST">
			<label for='username'>Username:</label>
			<input type="text" name="username" id="username">
			<br/>
			<label for='password'>Password:&nbsp;</label>
			<input type="password" name="password" id="password">
			<br/>
			<input type="submit" value="Log In" style="float: right;">
		</form>
	</div>
</body>