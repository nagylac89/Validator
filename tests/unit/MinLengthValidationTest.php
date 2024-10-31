<?php

declare(strict_types=1);

namespace Tests\Unit;

use Nagyl\Validator;

test(
	"min_length_validator_should_be_invalid",
	function () {
		$values = ["val" => "test"];

		$v = new Validator($values);
		$v->attribute("val")->minLength(5)->add();

		expect($v->validate())->toBeFalse();
	}
);


test("min_length_should_be_invalid_on_array_object", function () {
	$values = [
		"roles" => [
			["name" => "testflkwmnelfkmew"],
			["name" => "test"],
		]
	];

	$v = new Validator($values);
	$v->attribute("roles.*.name")->string()->minLength(5)->add();

	expect($v->validate())->toBeFalse();
});