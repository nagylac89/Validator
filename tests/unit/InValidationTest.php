<?php

declare(strict_types=1);

namespace Tests\Unit;

use Nagyl\Rules\InRule;
use Nagyl\Validator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class InValidationTest extends TestCase
{
	#[Test()]
	public function in_validation_should_valid()
	{
		$values = ["val"	=> 2];
		$rules =  ["val"	=> [InRule::create([2, 3, 4])]];

		$v = new Validator($values, $rules);

		$this->assertSame(true, $v->validate());
	}

	#[Test()]
	public function in_validation_should_failed()
	{
		$values = ["val"	=> 1];
		$rules =  ["val"	=> [InRule::create([2, 3, 4])]];

		$v = new Validator($values, $rules);

		$this->assertSame(false, $v->validate());
	}

	#[Test()]
	public function in_validation_failed_error()
	{
		$values = ["val"	=> 1];
		$rules =  ["val"	=> [InRule::create([2, 3, 4])]];

		$v = new Validator($values, $rules);
		$v->validate();

		$result = $v->result();

		$errorMsg = isset($result->errors["val"]) ? $result->errors["val"][0] : "";

		$this->assertSame("The val value does not in the enabled values! (2, 3, 4)", $errorMsg);
	}
}
