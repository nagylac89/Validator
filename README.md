Standalone validator library.

```php
<?php
$values = [
  "id"          => 1,
  "email"       => "email@test.com",
  "name"        => "Test Name",
  "avatar"      => null,
  "accepted"    => true,
  "permissions" => ["permission1", "permission2", "permission3"],
  "address"     => [
    "postalcode"  => 1212,
    "city"        => "City"
  ],
  "tests"  => [
    [ "id"  => 1, "name"  => "Test1" ],
    [ "id"  => 2, "name"  => "Test2" ]
  ]
];

$v = new Validator($values);

$v->attr("id")->int()->nullable()->add();
$v->attr("email")->string()->email()->required()->unique("users", "email")->add();
$v->attr("avatar")->string()->nullable()->add();
$v->attr("name")->string()->required()->maxLength(100)->add();
$v->attr("accepted")->boolean()->eq(true)->add();
$v->attr("permissions")->array()->string()->add();
$v->attr("address.postalcode")->int()->required()->add();
$v->attr("address.city")->string()->required()->add();
$v->attr("tests.*.id")->int()->exists("tests", "id")->add();
$v->attr("tests.*.name")->string()->add();

if (!$v->validate()) {
  $errors = $v->result()->errors;
} else {
  $validatedValues = $v->validatedValues();
}

?>
```

## Validator instance

```php
$inputValues = [
  "name" => "Test Name",
  "email" => "test@test.com"
];

$attributeNames = [
  "name"  => "Name",
  "email" => "E-mail"
];

$v = new Validator($inputValues, $attributeNames);
```

1. **_$inputValues_**: The input values to be validated.
2. **_$attributeNames_**: An optional array of names corresponding to the input values. Array keys should be equalant as $inputValues keys.

## Create a simple validation

```php
$inputValues = [
  "name" => "Test Name",
  "email" => "test@test.com",
  "roles" => [
    "id"  => 1, "name"  => "Role 1"
    "id"  => 2, "name"  => "Role 2"
  ]
];

// Create a Validator instance.
$v = new Validator($inputValues);

// Add validation rules.
//  selected attribute -> rules... -> add() => the add method needed for add queued rules
$v->attribute("name")->string()->required()->add();
$v->attribute("email")->string()->required()->add();
$v->attribute("roles.*.id")->int()->required()->exists("user_roles", "id")->add();
$v->attribute("roles.*.name")->string()->required()->add();


// $v->validate() check the validation, returns true or false.
if ($v->validate()) {
  // gets the validated values in specified types.
  $typedValued = $v->validatedValues();
} else {
  // gets the validation errors by attributes.
  $errors = $v->result()->errors;
}
```

## Available methods

### setQueryFetcher

```php
public static function setQueryFetcher(callable $func): void
```

For usage the exists and unique rules need the set a function which is responsible for executing sql queries.
This function has one parameter the string $queryString, which is the generated sql query.

#### Example:

```php
$db = new \SQLite3('test.sqlite');

Validator::setQueryFetcher(function (string $qs) use ($db) {
  $retval = [];

  $r = $db->query($qs);
  while ($result = $r->fetchArray()) {
    $retval[] = $result;
  }

  return $retval;
});
```

### setLang

```php
public static function setLang(string $lang): void
```

This is set the language code. Default language code is: "en".  
Available translations: en, hu, de, sk  
Need to set it before Validator instance is created.

### validate

```php
public function validate(bool $stopOnFirstError = false): bool
```

This run the validations.  
When the $stopOnFirstError parameter is true in this case the validation stops on the first failure.

### validatedValues

```php
public function validatedValues(): array
```

Get the validated values.

This method returns an array of values that have been validated.  
The array is structured is same as the input values and validation keys.  
If the inputValues contains not validated values, they will not be included in the returned array.  
The array values will be of specified types according to the validation rules.  
 e.g.: if the input value is "1" and the validation rule is int(), the validated value will be 1.

### result

```php
public function result(): ValidationResult
```

It returns the result of validation.

```php
class ValidationResult
{
  public bool $validated = false;
  public bool $isValid = true;
  public array $errors = []; // contains all errors by validation rule key
}
```

## Available rules in fluent assertion

In configuration of rules, the assertion should start with selection of attribute and should ends with the add() method which add the queued rules.

### attribute, attr, property, prop

```php
public function attribute(string $attribute): Validator;
public function attr(string $attribute): Validator;
public function property(string $attribute): Validator;
public function prop(string $attribute): Validator;
```

These methods are equivalent.  
This is the attribute selector.
e.g.: name, address.city, tags.\*.name...

### add

```php
public function add(): void
```

It close the ruleset configuration.

### withMessage

```php
public function withMessage(string $message): Validator
```

Add a custom error message for the previous rule.

#### Example:

```php
$v->attr("name")->string()->withMessage("The Name is not a string!")->add();
```

### string

```php
public function string(bool $safe = true): Validator
```

Add an string validation rule to the current attribute.  
$safe parameter is optional. If true, use xss protection for this attribute when call the validatedValues method.

### date

```php
public function date(string|array $format = []): Validator
```

Validates that the input is a date matching the specified format(s).  
string|array $format The date format(s) to validate against. Can be a single format as a string or multiple formats as an array.  
defaults: ["Y-m-d", "Y-m-d H:i", "Y-m-d H:i:s"]

### exists

```php
public function exists(string $table, string $column, ?string $additionalFilters = null): Validator
```

Checks if a record exists in the specified table and column with optional additional filters.  
For this rule to work, need to configure the query fetcher callback with this method setQueryFetcher.

#### example

```php
$v->attr("id")->int()->exists("users", "id", "delete_date is null")->add();
```

### unique

```php
public function unique(string $table, string $column, ?string $additionalFilters = null): Validator
```

Ensures that the value is unique in the specified database table and column.  
For this rule to work, need to configure the query fetcher callback with this method setQueryFetcher.

### when

```php
public function when(callable $condition, callable $otherRuleFunction): Validator
```

Applies a validation rule conditionally.  
This method allows you to apply a validation rule only when a specified condition is met.

#### example

```php
$v = new Validator([
  "id" => 1,
  "name" => "oksa"
]);

$v->attribute("name")->string()->when(
  fn($d) => $d["id"] !== null,
  fn($validator) => $validator->maxLength(2)
)->add();

```

### stopOnFailure

```php
public function stopOnFailure(): Validator
```

Stops the validation process on the first failure, on attribute level.

### must

```php
public function must(callable $func, string $errorMsg): Validator
```

Adds a custom validation rule to the validator.

#### example

```php
$v = new Validator([
  "name" => "oksa"
]);

$v->attribute("name")->string()->must(
  fn($d) => $d["name"] === "oksa",
  "The name value not equal with oksa!"
)->add();
```

### Other rules

```php
public function email(): Validator
```

```php
public function required(): Validator
```

```php
public function nullable(): Validator
```

```php
public function array(): Validator
```

```php
public function float(): Validator
```

```php
public function int(): Validator
```

```php
public function numeric(): Validator
```

```php
public function in(array $values): Validator
```

```php
public function minLength(int $length): Validator
```

```php
public function maxLength(int $length): Validator
```

```php
public function boolean(): Validator
```

```php
public function contains(string $value): Validator
```

```php
public function eq($value): Validator
```

```php
public function gt($value): Validator
```

```php
public function gte($value): Validator
```

```php
public function lt($value): Validator
```

```php
public function lte($value): Validator
```

```php
public function file(): Validator
```

```php
public function fileSize(int $maxSizeInKilobytes): Validator
```

```php
public function fileExt(array $extensions): Validator
```

```php
public function mimeType(array $mimeTypes): Validator
```
