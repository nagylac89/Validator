<?php

declare(strict_types=1);

namespace Tests\Unit;

use Nagyl\Validator;


test('boolean_validator_should_be_valid_on_booleans', function () {
	$values = [
		"val"	=> true,
		"val1"	=> false,
		"val2"	=> 1,
		"val3"	=> 0,
		"val4"	=> "1",
		"val5"	=> "0",
	];

	$v = new Validator($values);
	$v->attribute("val")->boolean()->add();
	$v->attribute("val1")->boolean()->add();
	$v->attribute("val2")->boolean()->add();
	$v->attribute("val3")->boolean()->add();
	$v->attribute("val4")->boolean()->add();
	$v->attribute("val5")->boolean()->add();

	expect($v->validate())->toBeTrue();
});

test('boolean_validator_should_be_invalid_on_null', function () {
	$values = [
		"val"	=> null
	];

	$v = new Validator($values);
	$v->attribute("val")->boolean()->add();

	expect($v->validate())->toBeFalse();
});

test('boolean_nullable_validator_should_be_valid_on_null', function () {
	$values = [
		"val"	=> null
	];

	$v = new Validator($values);
	$v->attribute("val")->boolean()->nullable()->add();

	expect($v->validate())->tobeTrue();
});
