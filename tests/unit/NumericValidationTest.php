<?php

declare(strict_types=1);

namespace Tests\Unit;

use Nagyl\Validator;

test("numeric_validator_should_be_valid_on_float", function () {
	$values = ["val" => 10.0];

	$v = new Validator($values);
	$v->attribute("val")->numeric()->add();

	expect($v->validate())->toBeTrue();
});

test("numeric_validator_should_be_valid_on_float_string", function () {
	$values = ["val" => "1.1"];

	$v = new Validator($values);
	$v->attribute("val")->numeric()->add();

	expect($v->validate())->toBeTrue();
});

test("numeric_validator_should_be_invalid_on_null", function () {
	$values = ["val" => null];

	$v = new Validator($values);
	$v->attribute("val")->numeric()->add();

	expect($v->validate())->toBeFalse();
});

test("numeric_validator_should_be_valid_on_int", function () {
	$values = ["val" => 1];

	$v = new Validator($values);
	$v->attribute("val")->numeric()->add();

	expect($v->validate())->toBeTrue();
});

test("numeric_validator_should_be_valid_when_nullable", function () {
	$values = ["val" => null];

	$v = new Validator($values);
	$v->attribute("val")->numeric()->nullable()->add();

	expect($v->validate())->toBeTrue();
});

test("numeric_validator_should_be_return_error", function () {
	$values = ["val" => ""];

	$v = new Validator($values);
	$v->attribute("val")->numeric()->add();
	$v->validate();
	$result = $v->result();

	$errorMsg = isset($result->errors["val"]) ? $result->errors["val"][0] : "";

	expect($errorMsg)->toBe("The val must be number!");
});


test("numeric_validation_should_be_invalid_on_array_object", function () {
	$values = [
		"roles" => [
			["name" => "admin", "id" => 1],
			["name" => "user", "id" => "null"],
		]
	];

	$v = new Validator($values);
	$v->attribute("roles.*.id")->numeric()->add();

	expect($v->validate())->toBeFalse();
});