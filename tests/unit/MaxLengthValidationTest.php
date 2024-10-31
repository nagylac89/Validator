<?php

declare(strict_types=1);

namespace Tests\Unit;

use Nagyl\Validator;

test(
	"max_length_validator_should_be_invalid",
	function () {
		$values = ["val" => "test text"];

		$v = new Validator($values);
		$v->attribute("val")->maxLength(5)->add();

		expect($v->validate())->toBeFalse();
	}
);

test(
	"max_length_validator_should_be_valid",
	function () {
		$values = ["val" => "test"];

		$v = new Validator($values);
		$v->attribute("val")->maxLength(5)->add();

		expect($v->validate())->tobeTrue();
	}
);

test('max_length_validator_error_msg_test', function () {
	$values = ["val" => "dddddddddd"];

	$v = new Validator($values);
	$v->attribute("val")->maxLength(5)->add();

	$v->validate();
	$result = $v->result();

	$errorMsg = isset($result->errors["val"]) ? $result->errors["val"][0] : "";

	expect($errorMsg)->toBe("The val should be maximum 5 character length!");
});

test("max_length_validator_should_be_valid_on_array_object", function () {
	$values = [
		"roles" => [
			["name" => "admindwdwdww"],
			["name" => "user"],
			["name" => "guest"]
		]
	];

	$v = new Validator($values);
	$v->attribute("roles.*.name")->maxLength(5)->add();

	expect($v->validate())->toBeFalse();
});