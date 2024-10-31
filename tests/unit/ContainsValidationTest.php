<?php

declare(strict_types=1);

namespace Tests\Unit;

use Nagyl\Validator;

test('contains_validator_should_be_valid', function () {
	$values = ["val" => "this is a test"];

	$v = new Validator($values);
	$v->attribute("val")->string()->contains("test")->add();

	expect($v->validate())->toBeTrue();
});

test('contains_validator_should_be_invalid_on_null', function () {
	$values = ["val" => null];

	$v = new Validator($values);
	$v->attribute("val")->string()->contains("test")->add();

	expect($v->validate())->toBeFalse();
});

test('contains_validator_should_be_invalid', function () {
	$values = ["val" => "this is a test"];

	$v = new Validator($values);
	$v->attribute("val")->string()->contains("test2")->add();

	expect($v->validate())->toBeFalse();
});

test("contains_test_on_array_object", function () {
	$values = [
		"roles" => [
			["name" => "admin role"],
			["name" => "user role"],
			["name" => "test role"]
		]
	];

	$v = new Validator($values);
	$v->attribute("roles.*.name")->contains("role")->add();

	expect($v->validate())->toBeTrue();
});