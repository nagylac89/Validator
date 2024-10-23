<?php

declare(strict_types=1);

namespace Tests\Unit;

use Nagyl\Rules\NullableRule;
use Nagyl\Rules\StringRule;
use Nagyl\Validator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class StringValidationTest extends TestCase
{
	#[Test()]
	public function string_validator_should_be_valid_on_string()
	{
		$values = ["name"	=> "test"];
		$rules =  ["name"	=> "string"];

		$v = new Validator($values, $rules);


		$this->assertSame(true, $v->validate());
	}

	#[Test()]
	public function string_validator_should_be_valid_on_empty_string()
	{
		$values = ["name"	=> ""];
		$rules =  ["name"	=> "string"];

		$v = new Validator($values, $rules);


		$this->assertSame(true, $v->validate());
	}

	#[Test()]
	public function string_validator_should_be_invalid_on_null()
	{
		$values = ["name"	=> null];
		$rules =  ["name"	=> "string"];

		$v = new Validator($values, $rules);

		$this->assertSame(false, $v->validate());
	}

	#[Test()]
	public function string_validator_should_be_valid_when_nullable()
	{
		$values = ["name"	=> null];
		$rules =  ["name"	=> "string|nullable"];

		$v = new Validator($values, $rules);

		$this->assertSame(true, $v->validate());
	}

	#[Test()]
	public function string_validator_should_be_return_error()
	{
		$values = ["name"	=> 1];
		$rules =  ["name"	=> "string"];

		$v = new Validator($values, $rules);
		$v->validate();
		$result = $v->result();

		$errorMsg = isset($result->errors["name"]) ? $result->errors["name"][0] : "";

		$this->assertSame("The name must be text!", $errorMsg);
	}

	#[Test()]
	public function string_create_validator_should_be_valid_on_string()
	{
		$values = ["name"	=> "teszt"];
		$rules =  ["name"	=> [StringRule::create()]];

		$v = new Validator($values, $rules);


		$this->assertSame(true, $v->validate());
	}

	#[Test()]
	public function string_create_validator_should_be_valid_on_empty_string()
	{
		$values = ["name"	=> ""];
		$rules =  ["name"	=> [StringRule::create()]];

		$v = new Validator($values, $rules);


		$this->assertSame(true, $v->validate());
	}

	#[Test()]
	public function string_create_validator_should_be_invalid_on_null()
	{
		$values = ["name"	=> null];
		$rules =  ["name"	=> [StringRule::create()]];

		$v = new Validator($values, $rules);

		$this->assertSame(false, $v->validate());
	}

	#[Test()]
	public function string_create_validator_should_be_valid_when_nullable()
	{
		$values = ["name"	=> null];
		$rules =  ["name"	=> [StringRule::create(), NullableRule::create()]];

		$v = new Validator($values, $rules);

		$this->assertSame(true, $v->validate());
	}

	#[Test()]
	public function string_create_validator_should_be_return_error()
	{
		$values = ["name"	=> 1];
		$rules =  ["name"	=> [StringRule::create()]];

		$v = new Validator($values, $rules);
		$v->validate();
		$result = $v->result();

		$errorMsg = isset($result->errors["name"]) ? $result->errors["name"][0] : "";

		$this->assertSame("The name must be text!", $errorMsg);
	}
}
