<?php

declare(strict_types=1);

namespace Tests\Unit;

use Nagyl\Validator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ArrayValidationTest extends TestCase
{
	#[Test()]
	public function array_validator_should_be_valid_on_array()
	{
		$values = ["val"	=> [1, 2, 3]];
		$rules =  ["val"	=> "array"];

		$v = new Validator($values, $rules);


		$this->assertSame(true, $v->validate());
	}

	#[Test()]
	public function array_validator_should_be_invalid_on_string()
	{
		$values = ["val"	=> "1.1"];
		$rules =  ["val"	=> "array"];

		$v = new Validator($values, $rules);

		$this->assertSame(false, $v->validate());
	}

	#[Test()]
	public function array_validator_should_be_invalid_on_null()
	{
		$values = ["val"	=> null];
		$rules =  ["val"	=> "array"];

		$v = new Validator($values, $rules);

		$this->assertSame(false, $v->validate());
	}

	#[Test()]
	public function array_nullable_validator_should_be_valid_on_null()
	{
		$values = ["val"	=> null];
		$rules =  ["val"	=> "array"];

		$v = new Validator($values, $rules);

		$this->assertSame(false, $v->validate());
	}

	#[Test()]
	public function float_validator_should_be_return_error()
	{
		$values = ["val"	=> ""];
		$rules =  ["val"	=> "array"];

		$v = new Validator($values, $rules);
		$v->validate();
		$result = $v->result();

		$errorMsg = isset($result->errors["val"]) ? $result->errors["val"][0] : "";

		$this->assertSame("The val must be array!", $errorMsg);
	}
}
