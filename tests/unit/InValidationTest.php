<?php

declare(strict_types=1);

namespace Tests\Unit;

use Nagyl\Rules\InRule;
use Nagyl\Validator;


test("in_validation_should_valid", function () {
	$values = ["val" => 2];

	$v = new Validator($values);
	$v->attribute("val")->in([2, 3, 4])->add();

	expect($v->validate())->toBeTrue();
});

test("in_validation_should_failed", function () {
	$values = ["val" => 1];

	$v = new Validator($values);
	$v->attribute("val")->in([2, 3, 4])->add();

	expect($v->validate())->tobeFalse();
});

test("in_validation_failed_error", function () {
	$values = ["val" => 1];

	$v = new Validator($values);
	$v->attribute("val")->in([2, 3, 4])->add();

	$v->validate();
	$result = $v->result();
	$errorMsg = isset($result->errors["val"]) ? $result->errors["val"][0] : "";

	expect($errorMsg)->toBe("The val value does not in the enabled values! (2, 3, 4)");
});

test("in_validation_test_on_array_object", function () {
	$values = [
		"roles" => [
			["id" => 1, "name" => "admin"],
			["id" => 2, "name" => "user"],
			["id" => 3, "name" => "guest"]
		]
	];

	$v = new Validator($values);
	$v->attribute("roles.*.id")->int()->in([1, 2, 3])->add();

	expect($v->validate())->toBeTrue();
});