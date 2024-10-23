<?php

declare(strict_types=1);

namespace Tests\Unit;

use Nagyl\Validator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class NumericValidationTest extends TestCase
{
	#[Test()]
	public function numeric_validator_should_be_valid_on_float()
	{
		$values = ["val"	=> 10.0];
		$rules =  ["val"	=> "numeric"];

		$v = new Validator($values, $rules);


		$this->assertSame(true, $v->validate());
	}

	#[Test()]
	public function numeric_validator_should_be_valid_on_float_string()
	{
		$values = ["val"	=> "1.1"];
		$rules =  ["val"	=> "numeric"];

		$v = new Validator($values, $rules);

		$this->assertSame(true, $v->validate());
	}

	#[Test()]
	public function numeric_validator_should_be_invalid_on_null()
	{
		$values = ["val"	=> null];
		$rules =  ["val"	=> "numeric"];

		$v = new Validator($values, $rules);

		$this->assertSame(false, $v->validate());
	}

	#[Test()]
	public function numeric_validator_should_be_valid_on_int()
	{
		$values = ["val"	=> 1];
		$rules =  ["val"	=> "numeric"];

		$v = new Validator($values, $rules);

		$this->assertSame(true, $v->validate());
	}

	#[Test()]
	public function numeric_validator_should_be_valid_when_nullable()
	{
		$values = ["val"	=> null];
		$rules =  ["val"	=> "numeric|nullable"];

		$v = new Validator($values, $rules);

		$this->assertSame(true, $v->validate());
	}

	#[Test()]
	public function numeric_validator_should_be_return_error()
	{
		$values = ["val"	=> ""];
		$rules =  ["val"	=> "numeric"];

		$v = new Validator($values, $rules);
		$v->validate();
		$result = $v->result();

		$errorMsg = isset($result->errors["val"]) ? $result->errors["val"][0] : "";

		$this->assertSame("The val must be number!", $errorMsg);
	}
}
