<?php

declare(strict_types=1);


namespace Nagyl;

use Exception;
use Nagyl\Rules\ArrayRule;
use Nagyl\Rules\FloatRule;
use Nagyl\Rules\InRule;
use Nagyl\Rules\IntRule;
use Nagyl\Rules\MinLengthRule;
use Nagyl\ValidationRule;
use Nagyl\Rules\StringRule;
use Nagyl\Rules\NullableRule;
use Nagyl\Rules\NumericRule;
use Nagyl\Rules\RequiredRule;
use Nagyl\Translation;

class Validator
{
	private ValidationResult $result;
	private array $rules = [];
	private $values;
	private Translation $translation;
	private array $_rule = [
		"attribute"	=> null,
		"rules"		=> []
	];

	public function __construct($values, string $lang = "en")
	{
		$this->result = new ValidationResult();
		$this->values = $values;

		$this->translation = new Translation();
		$this->translation->setLang($lang);
	}

	public function validate(): bool
	{
		$this->result->isValid = true;
		$this->result->errors = [];

		if (count($this->rules) > 0) {
			foreach ($this->rules as $key => $rules) {
				$attribute = $key;
				$value = isset($this->values[$attribute]) ? $this->values[$attribute] : null;

				foreach ($rules as $rule) {
					if ($rule instanceof ValidationRule) {
						if ($rule->validate($attribute, $value, $this->values, $rules) === false) {
							if (!isset($this->result->errors[$attribute])) {
								$this->result->errors[$attribute] = [];
							}
							$this->result->errors[$attribute][] = $rule->getMessage();
						}
					}
				}

				if (count($this->result->errors) > 0) {
					$this->result->isValid = false;
				}
			}
		}

		return $this->result->isValid;
	}

	public function result(): ValidationResult
	{
		return $this->result;
	}

	public function attribute(string $attribute): Validator
	{
		$this->_rule["attribute"] = $attribute;
		$this->_rule["rules"] = [];

		return $this;
	}

	public function add(): void
	{
		if (!empty($this->_rule["attribute"]) && count($this->_rule["rules"]) > 0) {
			$attribute = $this->_rule["attribute"];
			$rules = $this->_rule["rules"];

			if (isset($this->rules[$attribute])) {
				$this->rules[$attribute] = [...$this->rules[$attribute], ...$rules];
			} else {
				$this->rules[$attribute] = $rules;
			}
		}

		$this->_rule["attribute"] = null;
		$this->_rule["rules"] = [];
	}

	public function string(): Validator
	{
		$v = new StringRule();
		$v->translation = $this->translation;

		$this->_rule["rules"][] = $v;
		return $this;
	}

	public function required(): Validator
	{
		$v = new RequiredRule();
		$v->translation = $this->translation;

		$this->_rule["rules"][] = $v;
		return $this;
	}

	public function nullable(): Validator
	{
		$v = new NullableRule();
		$v->translation = $this->translation;

		$this->_rule["rules"][] = $v;
		return $this;
	}

	public function array(): Validator
	{
		$v = new ArrayRule();
		$v->translation = $this->translation;

		$this->_rule["rules"][] = $v;
		return $this;
	}

	public function float(): Validator
	{
		$v = new FloatRule();
		$v->translation = $this->translation;

		$this->_rule["rules"][] = $v;
		return $this;
	}

	public function int(): Validator
	{
		$v = new IntRule();
		$v->translation = $this->translation;

		$this->_rule["rules"][] = $v;
		return $this;
	}

	public function numeric(): Validator
	{
		$v = new NumericRule();
		$v->translation = $this->translation;

		$this->_rule["rules"][] = $v;
		return $this;
	}

	public function in(array $values): Validator
	{
		$v = new InRule($values);
		$v->translation = $this->translation;

		$this->_rule["rules"][] = $v;
		return $this;
	}

	public function minLength(int $length): Validator
	{
		$v = new MinLengthRule($length);
		$v->translation = $this->translation;

		$this->_rule["rules"][] = $v;
		return $this;
	}
}
