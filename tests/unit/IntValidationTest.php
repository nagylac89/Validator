<?php

declare(strict_types=1);

namespace Tests\Unit;

use Nagyl\Validator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class IntValidationTest extends TestCase
{
	#[Test()]
	public function int_validator_should_be_valid_on_int()
	{
		$values = ["val"	=> 10];
		$rules =  ["val"	=> "int"];

		$v = new Validator($values, $rules);


		$this->assertSame(true, $v->validate());
	}

	#[Test()]
	public function int_validator_should_be_valid_on_int_string()
	{
		$values = ["val"	=> "1"];
		$rules =  ["val"	=> "int"];

		$v = new Validator($values, $rules);

		$this->assertSame(true, $v->validate());
	}

	#[Test()]
	public function int_validator_should_be_invalid_on_null()
	{
		$values = ["val"	=> null];
		$rules =  ["val"	=> "int"];

		$v = new Validator($values, $rules);

		$this->assertSame(false, $v->validate());
	}

	#[Test()]
	public function int_validator_should_be_valid_when_nullable()
	{
		$values = ["val"	=> null];
		$rules =  ["val"	=> "int|nullable"];

		$v = new Validator($values, $rules);

		$this->assertSame(true, $v->validate());
	}

	#[Test()]
	public function int_validator_should_be_return_error()
	{
		$values = ["val"	=> ""];
		$rules =  ["val"	=> "int"];

		$v = new Validator($values, $rules);
		$v->validate();
		$result = $v->result();

		$errorMsg = isset($result->errors["val"]) ? $result->errors["val"][0] : "";

		$this->assertSame("The val must be integer!", $errorMsg);
	}
}
