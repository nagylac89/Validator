<?php

declare(strict_types=1);

namespace Tests\Unit;

use Nagyl\Validator;

test("required_validator_should_be_invalid_on_empty_string", function () {
	$values = ["val"	=> ""];

	$v = new Validator($values);
	$v->attribute("val")->string()->required()->add();

	expect($v->validate())->toBeFalse();
});

test("required_validator_should_be_valid_on_string", function () {
	$values = ["val"	=> "test"];

	$v = new Validator($values);
	$v->attribute("val")->string()->required()->add();

	expect($v->validate())->toBeTrue();
});

test("required_validator_should_be_invalid_on_null", function () {
	$values = ["val"	=> null];

	$v = new Validator($values);
	$v->attribute("val")->required()->add();

	expect($v->validate())->toBeFalse();
});

test("required_validator_should_be_invalid_on_empty_array", function () {
	$values = ["val"	=> []];

	$v = new Validator($values);
	$v->attribute("val")->required()->add();

	expect($v->validate())->toBeFalse();
});

test("required_validator_should_be_valid_on_array", function () {
	$values = ["val"	=> [1, 2, 3]];

	$v = new Validator($values);
	$v->attribute("val")->required()->add();

	expect($v->validate())->tobeTrue();
});

test("required_validator_should_be_return_error", function () {
	$values = ["val"	=> null];

	$v = new Validator($values);
	$v->attribute("val")->required()->add();

	$v->validate();
	$result = $v->result();

	$errorMsg = isset($result->errors["val"]) ? $result->errors["val"][0] : "";

	expect($errorMsg)->toBe("Required field: val!");
});
