Standalone validator library.

```php
<?php
$values = [
	"id"	=> 1,
	"email"	=> "email@test.com",
	"name"	=> "Test Name"
];

$v = new Validator($values);
$v->attribute("id")->int()->add(); // TODO: condition
$v->attribute("email")->string()->required()->add(); // todo: unique
$v->attribute("name")->string()->required()->maxLength(100)->add();

if (!$v->validate()) {
	$result = $v->result();

	$errors = $result->errors;
}

?>
```

## Rules:
