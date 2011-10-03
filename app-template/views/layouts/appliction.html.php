<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title><?= $page->title ?></title>
	<?= $page->scripts() ?>
	<?= $page->stylesheets() ?>
</head>
<body>
	<?= $page->content ?>
</body>
</html>