<?php

declare(strict_types=1);

namespace Tests\Unit;

use Nagyl\Validator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class RequiredValidationTest extends TestCase
{
	#[Test()]
	public function required_validator_should_be_invalid_on_empty_string()
	{
		$values = ["val"	=> ""];
		$rules =  ["val"	=> "string|required"];

		$v = new Validator($values, $rules);


		$this->assertSame(false, $v->validate());
	}

	#[Test()]
	public function required_validator_should_be_valid_on_string()
	{
		$values = ["val"	=> "test"];
		$rules =  ["val"	=> "string|required"];

		$v = new Validator($values, $rules);


		$this->assertSame(true, $v->validate());
	}

	#[Test()]
	public function required_validator_should_be_invalid_on_null()
	{
		$values = ["val"	=> null];
		$rules =  ["val"	=> "required"];

		$v = new Validator($values, $rules);


		$this->assertSame(false, $v->validate());
	}

	#[Test()]
	public function required_validator_should_be_invalid_on_empty_array()
	{
		$values = ["val"	=> []];
		$rules =  ["val"	=> "required"];

		$v = new Validator($values, $rules);


		$this->assertSame(false, $v->validate());
	}

	#[Test()]
	public function required_validator_should_be_valid_on_array()
	{
		$values = ["val"	=> [1, 2, 3]];
		$rules =  ["val"	=> "required"];

		$v = new Validator($values, $rules);


		$this->assertSame(true, $v->validate());
	}

	#[Test()]
	public function required_validator_should_be_return_error()
	{
		$values = ["val"	=> null];
		$rules =  ["val"	=> "required"];

		$v = new Validator($values, $rules);
		$v->validate();
		$result = $v->result();

		$errorMsg = isset($result->errors["val"]) ? $result->errors["val"][0] : "";

		$this->assertSame("Required field: val!", $errorMsg);
	}
}
