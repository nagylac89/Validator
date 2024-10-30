<?php

declare(strict_types=1);

namespace Tests\Unit;

use Nagyl\Validator;

test("less_then_validation_should_be_valid_on_int", function () {
	$v = new Validator([
		"number" => 10
	]);

	$v->attribute("number")->int()->lt(11)->add();

	expect($v->validate())->toBeTrue();
});

test("less_then_validation_should_be_valid_on_float", function () {
	$v = new Validator([
		"number" => 10.5
	]);

	$v->attribute("number")->float()->lt(11.1)->add();

	expect($v->validate())->toBeTrue();
});

test("less_then_validation_should_be_valid_on_numeric", function () {
	$v = new Validator([
		"number" => 11
	]);

	$v->attribute("number")->numeric()->lt(12.1)->add();

	expect($v->validate())->toBeTrue();
});

test("less_then_validation_should_be_valid_on_dates", function () {
	$d2 = \DateTime::createFromFormat("Y-m-d", "2024-01-03");

	$v = new Validator([
		"date" => \DateTime::createFromFormat("Y-m-d", "2024-01-02")
	]);

	$v->attribute("date")->date()->lt($d2)->add();

	expect($v->validate())->toBeTrue();
});

test("less_then_validation_should_be_valid_on_datetimes", function () {
	$d2 = \DateTime::createFromFormat("Y-m-d H:i", "2024-01-01 11:00");

	$v = new Validator([
		"date" => \DateTime::createFromFormat("Y-m-d H:i", "2024-01-01 10:00")
	]);

	$v->attribute("date")->date()->lt($d2)->add();

	expect($v->validate())->toBeTrue();
});

test("less_then_validation_should_be_valid_on_datetimes_strings", function () {
	$v = new Validator([
		"date" => "2024-01-01 10:00"
	]);

	$v->attribute("date")->date()->lt("2024-01-01 11:00")->add();

	expect($v->validate())->toBeTrue();
});

test("less_then_validation_should_be_valid_on_datetimes_msg", function () {
	$v = new Validator([
		"date" => "2024-01-02"
	]);

	$v->attribute("date")->date()->lt("2024-01-01")->add();
	$v->validate();

	$msg = $v->result()->errors["date"][0];

	expect($msg)->toBe("The date value should be less than 2024-01-01!");
});

test("less_then_or_equal_validation_should_be_valid_on_int", function () {
	$v = new Validator([
		"number" => 5
	]);

	$v->attribute("number")->int()->lte(5)->add();

	expect($v->validate())->toBeTrue();
});

test("less_then_or_equal_validation_should_be_valid_on_float", function () {
	$v = new Validator([
		"number" => 10.5
	]);

	$v->attribute("number")->float()->lte(10.5)->add();

	expect($v->validate())->toBeTrue();
});

test("less_then_or_equal_validation_should_be_valid_on_numeric", function () {
	$v = new Validator([
		"number" => 11
	]);

	$v->attribute("number")->numeric()->lte(11)->add();

	expect($v->validate())->toBeTrue();
});