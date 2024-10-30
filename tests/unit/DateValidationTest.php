<?php

declare(strict_types=1);

namespace Tests\Unit;

use DateTime;
use Nagyl\Validator;


test('date_validator_should_be_valid_on_date_string', function () {
	$values = ["val" => "2024-10-11"];

	$v = new Validator($values);
	$v->attribute("val")->date()->add();

	expect($v->validate())->toBeTrue();
});

test('date_validator_should_be_valid_on_datetime_string', function () {
	$values = ["val" => "2024-10-11 10:00"];

	$v = new Validator($values);
	$v->attribute("val")->date()->add();

	expect($v->validate())->toBeTrue();
});

test('date_validator_should_be_valid_on_custom_format', function () {
	$values = ["val" => "2024.10.10."];

	$v = new Validator($values);
	$v->attribute("val")->date("Y.m.d.")->add();

	expect($v->validate())->toBeTrue();
});

test('date_validator_should_be_valid_on_datetime_obj', function () {
	$values = ["val" => new DateTime()];

	$v = new Validator($values);
	$v->attribute("val")->date()->add();

	expect($v->validate())->toBeTrue();
});

test('date_validator_should_be_invalid_on_null', function () {
	$values = ["val" => null];

	$v = new Validator($values);
	$v->attribute("val")->date()->add();

	expect($v->validate())->toBeFalse();
});

test('date_nullable_validator_should_be_valid_on_null', function () {
	$values = ["val" => null];

	$v = new Validator($values);
	$v->attribute("val")->date()->nullable()->add();

	expect($v->validate())->toBeTrue();
});

test("date_parser_times_shoud_be_zero_when_not_used", function () {
	$values = ["val" => "2024-10-11"];

	$v = new Validator($values);
	$v->attribute("val")->date()->add();

	$v->validate();
	$date = null;

	if ($v->result()->isValid) {
		$model = $v->validatedValues();
		$date = $model["val"]->format("Y-m-d H:i:s");
	}

	expect($date)->toBe("2024-10-11 00:00:00");
});