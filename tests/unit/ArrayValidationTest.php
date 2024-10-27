<?php

declare(strict_types=1);

namespace Tests\Unit;

use Nagyl\Validator;


test('array_validator_should_be_valid_on_array', function () {
	$values = ["val" => [1, 2, 3]];

	$v = new Validator($values);
	$v->attribute("val")->array()->add();


	expect($v->validate())->toBeTrue();
});


test('array_validator_should_be_invalid_on_string', function () {
	$values = ["val" => "1.1"];

	$v = new Validator($values);
	$v->attribute("val")->array()->add();

	expect($v->validate())->toBeFalse();
});

test('array_validator_should_be_invalid_on_null', function () {
	$values = ["val" => null];

	$v = new Validator($values);
	$v->attribute("val")->array()->add();

	expect($v->validate())->toBeFalse();
});

test('array_nullable_validator_should_be_valid_on_null', function () {
	$values = ["val" => null];

	$v = new Validator($values);
	$v->attribute("val")->array()->nullable()->add();

	expect($v->validate())->toBeTrue();
});

test('float_validator_should_be_return_error', function () {
	$values = ["val" => ""];

	$v = new Validator($values);
	$v->attribute("val")->array()->add();

	$v->validate();
	$result = $v->result();

	$errorMsg = isset($result->errors["val"]) ? $result->errors["val"][0] : "";

	expect($errorMsg)->toBe("The val must be array!");
});

test("array_int_validator_should_be_valid_on_int_values", function () {
	$values = ["val" => [1, 2, 3]];

	$v = new Validator($values);
	$v->attribute("val")->array()->int()->add();

	expect($v->validate())->toBeTrue();
});

test("array_validator_should_be_valid_on_string_values", function () {
	$values = ["val" => ["test 1", "test null", "test 3"]];

	$v = new Validator($values);
	$v->attribute("val")->array()->string()->add();

	expect($v->validate())->toBeTrue();
});

test("array_validator_should_be_valid_on_float_values", function () {
	$values = ["val" => [1.1, 2.2, 3.3]];

	$v = new Validator($values);
	$v->attribute("val")->array()->float()->add();

	expect($v->validate())->toBeTrue();
});

test("array_validator_should_be_valid_on_numeric_values", function () {
	$values = ["val" => [1, 2.2, 3.3]];

	$v = new Validator($values);
	$v->attribute("val")->array()->numeric()->add();

	expect($v->validate())->toBeTrue();
});

test("array_validator_should_be_valid_on_boolead_values", function () {
	$values = ["val" => [true, false, false]];

	$v = new Validator($values);
	$v->attribute("val")->array()->boolean()->add();

	expect($v->validate())->toBeTrue();
});

test("array_validator_should_be_valid_on_date_values", function () {
	$values = ["val" => ["2024-10-01", "2024-10-02", "2024-10-03"]];

	$v = new Validator($values);
	$v->attribute("val")->array()->date()->add();

	expect($v->validate())->toBeTrue();
});