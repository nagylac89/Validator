<?php

declare(strict_types=1);

namespace Tests\Unit;

use Nagyl\Validator;


test("int_validator_should_be_valid_on_int", function () {
	$values = ["val"	=> 10];

	$v = new Validator($values);
	$v->attribute("val")->int()->add();

	expect($v->validate())->toBeTrue();
});

test("int_validator_should_be_valid_on_int_string", function () {
	$values = ["val"	=> "1"];

	$v = new Validator($values);
	$v->attribute("val")->int()->add();

	expect($v->validate())->toBeTrue();
});

test("int_validator_should_be_invalid_on_null", function () {
	$values = ["val"	=> null];

	$v = new Validator($values);
	$v->attribute("val")->int()->add();

	expect($v->validate())->toBeFalse();
});

test("int_validator_should_be_valid_when_nullable", function () {
	$values = ["val"	=> null];

	$v = new Validator($values);
	$v->attribute("val")->int()->nullable()->add();

	expect($v->validate())->toBeTrue();
});

test("int_validator_should_be_return_error", function () {
	$values = ["val"	=> ""];

	$v = new Validator($values);
	$v->attribute("val")->int()->add();

	$v->validate();
	$result = $v->result();

	$errorMsg = isset($result->errors["val"]) ? $result->errors["val"][0] : "";

	expect($errorMsg)->toBe("The val must be integer!");
});
