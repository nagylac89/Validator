<?php

declare(strict_types=1);

namespace Tests\Unit;

use Nagyl\Validator;

pest()->beforeEach(function () {
	Validator::setLang("en");
})->afterEach(function () {
	Validator::setLang("en");
});


test("stop_on_first_throws_only_one_error", function () {
	$v = new Validator([
		"name" => "teszt"
	]);

	$v->attribute("name")->int()->in([1, 2, 3])->add();
	$v->validate(true);
	$r = $v->result();


	expect(count($r->errors["name"]))->toBe(1);
});

test("when_true_should_be_valid", function () {
	$v = new Validator([
		"id" => 1,
		"name" => "oksa"
	]);

	$v->attribute("name")->string()->when(
		fn($d) => $d["id"] !== null,
		fn($validator) => $validator->maxLength(2)
	)->add();


	expect($v->validate())->toBeFalse();
});

test("eq_rule_should_be_valid", function () {
	$v = new Validator([
		"name" => "oksa"
	]);

	$v->attribute("name")->string()->eq("oksa")->add();

	expect($v->validate())->toBeTrue();
});

test("eq_rule_should_be_invalid", function () {
	$v = new Validator([
		"name" => "oksa"
	]);

	$v->attribute("name")->string()->eq("ok")->add();
	$v->validate();

	$result = $v->result();

	expect($result->errors["name"][0])->toBe("The name value not equal with ok!");
});

test("must_rule_should_be_valid", function () {
	$v = new Validator([
		"name" => "oksa"
	]);

	$v->attribute("name")->string()->must(fn($d) => $d["name"] === "oksa", "The name value not equal with oksa!")->add();

	expect($v->validate())->toBeTrue();
});

test("must_rule_error_msg_check", function () {
	$v = new Validator([
		"name" => "oksa"
	]);
	$v->attribute("name")->string()->must(fn($d) => $d["name"] === "OK", "The name value not equal with oksa!")->add();
	$v->validate();
	$errorMsg = $v->result()->errors["name"][0];

	expect($errorMsg)->toBe("The name value not equal with oksa!");
});

test("test_name_attributes", function (string $attribute, string $name) {

	$values = [
		// "name" => null,
		// "email" => null,
		"roles" => [
			["id" => null, "name" => null],
			["id" => 1, "name" => "user"]
		],
		// "address" => [
		// 	"city" => null,
		// 	"street" => null,
		// 	"type" => [
		// 		"name" => null
		// 	]
		// ]
	];

	$attributeNames = [
		"name" => "Name",
		"email" => "E-mail",
		"roles" => "Roles",
		"roles.*.id" => "Role ID",
		"roles.*.name" => " Role Name",
		"address" => "Address",
		"address.city" => "City",
		"address.street" => "Street",
		"address.type" => "Adress Type",
		"address.type.name" => "Address Type Name",
	];

	$v = new Validator($values, $attributeNames);

	$v->attr("name")->string()->required()->add();
	$v->attr("email")->string()->required()->add();
	$v->attr("roles")->array()->required()->add();
	$v->attr("roles.*.id")->int()->required()->add();
	$v->attr("roles.*.name")->string()->required()->add();
	$v->attr("address")->required()->add();
	$v->attr("address.city")->string()->required()->add();
	$v->attr("address.street")->string()->required()->add();
	$v->attr("address.type.name")->string()->required()->add();

	$v->validate();

	$errors = $v->result()->errors;

	expect($errors[$attribute][0])->toContain($name);
})->with([
			["name", "Name"],
			["email", "E-mail"],
			["roles.*.id", "Role ID"],
			["roles.*.name", " Role Name"],
			["address", "Address"],
			["address.city", "City"],
			["address.street", "Street"],
			["address.type.name", "Address Type Name"],
		]);


test("test_custom_message", function () {
	$v = new Validator([
		"name" => null
	]);

	$v->attr("name")->required()->withMessage("Custom message!")->string()->add();
	$v->validate();

	$errors = $v->result()->errors;

	expect($errors["name"][0])->toBe("Custom message!");
});

test("test_hungarian_required_error_msg", function () {
	Validator::setLang("hu");

	$v = new Validator([
		"name" => null
	], [
		"name" => "Név"
	]);

	$v->attr("name")->required()->string()->add();
	$v->validate();

	$errors = $v->result()->errors;

	expect($errors["name"][0])->toBe("Kötelező mező: Név!");
});

test('test_get_validated_model', function () {
	$v = new Validator([
		"name" => "Name",
		"email" => "Email@oksa.hu",
		"roles" => [
			[
				"id" => 1,
				"name" => "Admin",
				"permissions" => ["create", "update"],
				"creator" => [
					"id" => 1,
					"name" => "User Name"
				]
			],
			[
				"id" => 2,
				"name" => "User",
				"permissions" => ["asd1"],
				"optional" => "oksa",
				"creator" => [
					"id" => 2,
					"name" => "User Name 2"
				]
			]
		],
		"address" => [
			"city" => "Budapest",
			"street" => "Váci utca",
			"type" => [
				"name" => "Home",
				"tags" => ["tag1", "tag2"]
			]
		],
		"age" => 30,
		"age2" => 30.456,
		"lastPoint" => 30.22,
		"active" => true,
		"points" => [10, 20, "20.5", 30.5],
		"array_string" => ["a", null, "c"],
		"array_date" => ["2021-01-01", "2021-01-02"],
		"created_at" => "2021-01-01 10:00:00",
		"date_of_birth" => "1990-01-01",
		"custom_prop_1" => "custom_1",
		"custom_prop_2" => "custom_1",
		"custom_prop_3" => "custom_1",
		"custom_prop_4" => "custom_1",
	]);

	$v->attr("name")->string()->required()->add();
	$v->attr("email")->string()->required()->add();
	$v->attr("roles.*.id")->stopOnFailure()->required()->int()->add();
	$v->attr("roles.*.name")->stopOnFailure()->required()->string()->add();
	$v->attr("roles.*.optional")->string()->nullable()->add();
	$v->attr("roles.*.creator.id")->int()->add();
	$v->attr("roles.*.creator.name")->string()->add();
	$v->attr("roles.*.permissions")->array()->string()->add();
	$v->attr("address.city")->string()->required()->add();
	$v->attr("address.street")->string()->required()->add();
	$v->attr("address.type.name")->string()->required()->add();
	$v->attr("address.type.tags")->array()->string()->required()->add();
	$v->attr("age")->int()->required()->add();
	$v->attr("age2")->float()->required()->add();
	$v->attr("lastPoint")->numeric()->required()->add();
	$v->attr("active")->boolean()->required()->add();
	$v->attr("points")->array()->required()->numeric()->add();
	$v->attr("array_string")->array()->string()->nullable()->add();
	$v->attr("created_at")->date("Y-m-d H:i:s")->required()->add();
	$v->attr("date_of_birth")->date("Y-m-d")->required()->add();
	$v->attr("array_date")->array()->date("Y-m-d")->required()->add();

	$v->validate();
	$model = [];

	if ($v->result()->isValid) {
		$model = $v->validatedValues();
	}

	expect($model["name"])->toBe("Name");
	expect($model["roles"])->toBeArray();
	expect($model["roles"][0]["id"])->toBe(1);
	expect($model["roles"][0]["creator"]["id"])->toBe(1);
	expect($model["roles"][0]["permissions"])->toBeArray();
	expect($model["address"]["type"]["tags"])->toBeArray();
	expect(count($model["address"]["type"]["tags"]))->toBe(2);
	expect(array_keys($model))->not()->toContain("custom_prop_1");
	expect($model["points"])->toBeArray()->each()->toBeNumeric();
	expect($model["array_date"])->toBeArray()->each()->toBeInstanceOf(\DateTime::class);
	expect($model["created_at"])->toBeInstanceOf(\DateTime::class);
});