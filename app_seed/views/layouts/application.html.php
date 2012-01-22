<!DOCTYPE html>
<html lang="<?= $this->response->language ?>">
<head>
	<meta charset="<?= $this->response->charset ?>">
	<title>Application Seed</title>
	<style>
		body {
			background-color: #fff;
			border-radius: 5px;
			box-shadow: 0 0 15px #ccc;
			color: #000;
			font: 100%/1.5 Helvetica, Arial, sans-serif;
			margin: 3em auto;
			padding: 5px 30px 15px;
			width: 600px;
		}
	</style>
	<? $this->stylesheets() ?>
	<? $this->scripts() ?>
</head>
<body>
	<? echo $this->content; ?>
</body>
</html>