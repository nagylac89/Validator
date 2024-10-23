<?php

declare(strict_types=1);


namespace Nagyl;

use Exception;
use Nagyl\Rules\ArrayRule;
use Nagyl\Rules\FloatRule;
use Nagyl\Rules\IntRule;
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

	public function __construct($values, array $rules, string $lang = "en")
	{
		$this->result = new ValidationResult();
		$this->values = $values;

		$this->translation = new Translation();
		$this->translation->setLang($lang);

		$this->parseRules($rules);
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
					$r = $rule["instance"];

					if ($r instanceof ValidationRule) {
						if ($r->validate($attribute, $value, $this->values, $rules) === false) {
							if (!isset($this->result->errors[$attribute])) {
								$this->result->errors[$attribute] = [];
							}
							$this->result->errors[$attribute][] = $r->getMessage();
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

	private function allRules(): array
	{
		$retval = [
			"string"	=> StringRule::class,
			"nullable"	=> NullableRule::class,
			"int"		=> IntRule::class,
			"float"		=> FloatRule::class,
			"numeric"	=> NumericRule::class,
			"required"	=> RequiredRule::class,
			"array"		=> ArrayRule::class
		];

		return $retval;
	}

	private function parseRules(array $rules): void
	{
		if (count($rules) > 0) {
			$definedRules = $this->allRules();

			foreach ($rules as $key => $rule) {
				if (is_string($rule)) {
					$ruleParts = explode("|", $rule);
					$parsedRule = [];

					foreach ($ruleParts as $p) {
						if (isset($definedRules[$p])) {
							$className =  $definedRules[$p];

							try {
								$r = new $className();
								if ($r instanceof ValidationRule) {
									$r->translation = $this->translation;
									$r->params = [
										"attribute"	=> $key
									];

									$parsedRule[] = [
										"instance"	=> $r,
									];
								}
							} catch (Exception $ex) {
							}
						}
					}

					$this->rules[$key] = $parsedRule;
				} else if (is_array($rule)) {
				}
			}
		}
	}

	public function result(): ValidationResult
	{
		return $this->result;
	}
}