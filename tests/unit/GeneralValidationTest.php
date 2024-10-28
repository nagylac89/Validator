<?php

declare(strict_types=1);

namespace Tests\Unit;

use Nagyl\Validator;

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
		"name" => null,
		"email" => null,
		"roles" => [
			["id" => 1, "name" => null],
			["id" => 2, "name" => "user"]
		],
		"address" => [
			"city" => null,
			"street" => null,
			"type" => [
				"name" => null
			]
		]
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
	$v->attr("roles.*.id")->array()->int()->required()->add();
	$v->attr("roles.*.name")->array()->string()->required()->add();
	$v->attr("address")->array()->required()->add();
	$v->attr("address.city")->string()->required()->add();
	$v->attr("address.street")->string()->required()->add();
	$v->attr("address.type.name")->string()->required()->add();

	$v->validate();

	$errors = $v->result()->errors;

	expect($errors[$attribute][0])->toContain($name);
})->with([
			["name", "Name"],
			["email", "E-mail"],
			["roles", "Roles"],
			["roles.*.id", "Role ID"],
			["roles.*.name", " Role Name"],
			["address", "Address"],
			["address.city", "City"],
			["address.street", "Street"],
			["address.type.name", "Address Type Name"],
		]);