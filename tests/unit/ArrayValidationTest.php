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

test("array_validator_should_be_valid_on_eq_values", function () {
	$values = ["val" => [true, true, true]];

	$v = new Validator($values);
	$v->attribute("val")->array()->eq(true)->add();

	expect($v->validate())->toBeTrue();
});

test("array_validator_should_be_valid_on_contains_values", function () {
	$values = ["val" => ["test 1", "test 2", "test 2"]];

	$v = new Validator($values);
	$v->attribute("val")->array()->string()->contains("test")->add();

	expect($v->validate())->toBeTrue();
});

test("array_validator_should_be_valid_on_gt_values", function () {
	$values = ["val" => [5, 5, 6, 10]];

	$v = new Validator($values);
	$v->attribute("val")->array()->int()->gt(4)->add();

	expect($v->validate())->toBeTrue();
});

test("array_validator_should_be_valid_on_gte_values", function () {
	$values = ["val" => [5, 5, 6, 10]];

	$v = new Validator($values);
	$v->attribute("val")->array()->int()->gte(5)->add();

	expect($v->validate())->toBeTrue();
});

test("array_validator_should_be_valid_on_lt_values", function () {
	$values = ["val" => [5, 5, 6, 10]];

	$v = new Validator($values);
	$v->attribute("val")->array()->int()->lt(11)->add();

	expect($v->validate())->toBeTrue();
});

test("array_validator_should_be_valid_on_lte_values", function () {
	$values = ["val" => [5, 5, 6, 10]];

	$v = new Validator($values);
	$v->attribute("val")->array()->int()->lte(10)->add();

	expect($v->validate())->toBeTrue();
});

test("array_validator_should_be_valid_on_maxlength_values", function () {
	$values = ["val" => ["abc", "def", "ghi"]];

	$v = new Validator($values);
	$v->attribute("val")->array()->string()->maxLength(3)->add();

	expect($v->validate())->toBeTrue();
});

test("array_validator_should_be_valid_on_minlength_values", function () {
	$values = ["val" => ["abc", "def", "ghi"]];

	$v = new Validator($values);
	$v->attribute("val")->array()->string()->minLength(1)->add();

	expect($v->validate())->toBeTrue();
});


test("array_validator_should_be_valid_on_in_values", function () {
	$values = ["val" => [1, 2, 3, 3, 3]];

	$v = new Validator($values);
	$v->attribute("val")->array()->in([1, 2, 3])->add();

	expect($v->validate())->toBeTrue();
});


test("array_validator_should_be_valid_on_required_values", function () {
	$values = ["val" => [1, 2, null, 3, 3]];

	$v = new Validator($values);
	$v->attribute("val")->array()->required()->add();

	expect($v->validate())->toBeFalse();
});

