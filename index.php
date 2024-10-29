<?php
require "./vendor/autoload.php";

use Nagyl\Rules\StringRule;
use Nagyl\Validator;

Validator::setLang("hu");

if (isset($_POST["p"])) {
	$v = new Validator($_POST + $_FILES, [
		"name" => "Név",
		"email" => "E-mail",
		"uploadfile" => "Fájl"
	]);

	$v->attribute("name")->string()->minLength(5)->maxLength(20)->required()->add();
	$v->attribute("email")->string()->minLength(10)->maxLength(20)->required()->add();
	$v->attribute("uploadfile")->file()->fileSize(1024)->fileExt(["png", "jpg"])->mimeType(["image/jpeg", "image/png"])->add();

	if ($v->validate()) {
		echo "OK";
	} else {
		echo '<pre>';
		var_dump($v->result());
		echo '</pre>';
	}
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Document</title>
</head>

<body>

	<form action="index.php" method="post" enctype="multipart/form-data">
		<div style="margin-bottom:5px;"><input type="text" name="name" id="name" placeholder="Name" /></div>
		<div style="margin-bottom:5px;"><input type="text" name="email" id="email" placeholder="Email" /></div>
		<div style="margin-bottom:5px;"><input type="file" name="uploadfile" /></div>

		<button type="submit" name="p" value="1">Submit</button>
	</form>
</body>

</html>