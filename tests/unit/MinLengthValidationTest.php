<?php

declare(strict_types=1);

namespace Tests\Unit;

use Nagyl\Validator;

test(
	"min_length_validator_should_be_invalid",
	function () {
		$values = ["val"	=> "test"];

		$v = new Validator($values);
		$v->attribute("val")->minLength(5)->add();

		expect($v->validate())->toBeFalse();
	}
);
