<?php

declare(strict_types=1);

namespace Tests\Unit;

use Nagyl\Validator;

test("string_validator_should_be_valid_on_string", function () {
	$values = ["name" => "test"];

	$v = new Validator($values);
	$v->attribute("name")->string()->add();

	$this->assertSame(true, $v->validate());
	expect($v->validate())->toBeTrue();
});

test("string_validator_should_be_valid_on_empty_string", function () {
	$values = ["name" => ""];

	$v = new Validator($values);
	$v->attribute("name")->string()->add();

	expect($v->validate())->toBeTrue();
});

test("string_validator_should_be_invalid_on_null", function () {
	$values = ["name" => null];

	$v = new Validator($values);
	$v->attribute("name")->string()->add();

	expect($v->validate())->toBeFalse();
});

test("string_validator_should_be_valid_when_nullable", function () {
	$values = ["name" => null];

	$v = new Validator($values);
	$v->attribute("name")->string()->nullable()->add();

	expect($v->validate())->toBeTrue();
});

test("string_validator_should_be_return_error", function () {
	$values = ["name" => 1];

	$v = new Validator($values);
	$v->attribute("name")->string()->add();

	$v->validate();
	$result = $v->result();
	$errorMsg = isset($result->errors["name"]) ? $result->errors["name"][0] : "";

	expect($errorMsg)->toBe("The name must be text!");
});

test("string_array_object_validator_should_be_valid", function () {
	$values = [
		"roles" => [
			["name" => "name"],
			["name" => "user"],
		]
	];

	$v = new Validator($values);
	$v->attr("roles.*.name")->string()->add();

	expect($v->validate())->toBeTrue();
});

test("string_validated_model_xss_protected", function () {
	$values = ["name" => "<script>alert('xss')</script>"];

	$v = new Validator($values);
	$v->attribute("name")->string()->add();
	$v->validate();

	$model = $v->validatedValues();

	expect($model["name"])->toBe("&lt;script&gt;alert(&#039;xss&#039;)&lt;/script&gt;");
});

test("string_validated_model_xss_protection_off", function () {
	$values = ["name" => "<script>alert('xss')</script>"];

	$v = new Validator($values);
	$v->attribute("name")->string(safe: false)->add();
	$v->validate();

	$model = $v->validatedValues();

	expect($model["name"])->toBe("<script>alert('xss')</script>");
});