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