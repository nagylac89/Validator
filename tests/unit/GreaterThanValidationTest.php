<?php

declare(strict_types=1);

namespace Tests\Unit;

use Nagyl\Validator;

test("greater_then_validation_should_be_valid_on_int", function () {
	$v = new Validator([
		"number" => 10
	]);

	$v->attribute("number")->int()->gt(5)->add();

	expect($v->validate())->toBeTrue();
});

test("greater_then_validation_should_be_valid_on_float", function () {
	$v = new Validator([
		"number" => 10.5
	]);

	$v->attribute("number")->float()->gt(10.1)->add();

	expect($v->validate())->toBeTrue();
});

test("greater_then_validation_should_be_valid_on_numeric", function () {
	$v = new Validator([
		"number" => 11
	]);

	$v->attribute("number")->numeric()->gt(10.1)->add();

	expect($v->validate())->toBeTrue();
});

test("greater_then_validation_should_be_valid_on_dates", function () {
	$d2 = \DateTime::createFromFormat("Y-m-d", "2024-01-01");

	$v = new Validator([
		"date" => \DateTime::createFromFormat("Y-m-d", "2024-01-02")
	]);

	$v->attribute("date")->date()->gt($d2)->add();

	expect($v->validate())->toBeTrue();
});

test("greater_then_validation_should_be_valid_on_datetimes", function () {
	$d2 = \DateTime::createFromFormat("Y-m-d H:i", "2024-01-01 09:00");

	$v = new Validator([
		"date" => \DateTime::createFromFormat("Y-m-d H:i", "2024-01-01 10:00")
	]);

	$v->attribute("date")->date()->gt($d2)->add();

	expect($v->validate())->toBeTrue();
});

test("greater_then_validation_should_be_valid_on_datetimes_strings", function () {
	$v = new Validator([
		"date" => "2024-01-01 10:00"
	]);

	$v->attribute("date")->date()->gt("2024-01-01 09:00")->add();

	expect($v->validate())->toBeTrue();
});

test("greater_then_validation_should_be_valid_on_datetimes_msg", function () {
	$v = new Validator([
		"date" => "2023-01-01"
	]);

	$v->attribute("date")->date()->gt("2024-01-01")->add();
	$v->validate();

	$msg = $v->result()->errors["date"][0];

	expect($msg)->toBe("The date value should be greater than 2024-01-01!");
});

test("greater_then_or_equal_validation_should_be_valid_on_int", function () {
	$v = new Validator([
		"number" => 5
	]);

	$v->attribute("number")->int()->gte(5)->add();

	expect($v->validate())->toBeTrue();
});

test("greater_then_or_equal_validation_should_be_valid_on_float", function () {
	$v = new Validator([
		"number" => 10.5
	]);

	$v->attribute("number")->float()->gte(10.5)->add();

	expect($v->validate())->toBeTrue();
});

test("greater_then_or_equal_validation_should_be_valid_on_numeric", function () {
	$v = new Validator([
		"number" => 11
	]);

	$v->attribute("number")->numeric()->gte(11)->add();

	expect($v->validate())->toBeTrue();
});