<?php

declare(strict_types=1);


namespace Nagyl;

use Exception;
use Nagyl\Rules\ArrayRule;
use Nagyl\Rules\FloatRule;
use Nagyl\Rules\InRule;
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

	public function result(): ValidationResult
	{
		return $this->result;
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
			"array"		=> ArrayRule::class,
			"in"		=> InRule::class
		];

		return $retval;
	}

	private function parseRuleFromString(string $ruleString, string $attribute): ?ValidationRule
	{
		$definedRules = $this->allRules();

		if (isset($definedRules[$ruleString])) {
			$className =  $definedRules[$ruleString];

			try {
				$r = new $className();
				if ($r instanceof ValidationRule) {
					$r->translation = $this->translation;

					$r->params = [
						"attribute"	=> $attribute
					];

					return $r;
				}
			} catch (Exception $ex) {
			}
		}
		return null;
	}


	private function parseRules(array $rules): void
	{
		if (count($rules) > 0) {
			foreach ($rules as $key => $rule) {
				if (!isset($this->rules[$key])) {
					$this->rules[$key] = [];
				}

				if (is_string($rule)) {
					$ruleParts = explode("|", $rule);

					foreach ($ruleParts as $p) {
						$r = $this->parseRuleFromString($p, $key);

						if ($r !== null) {
							$this->rules[$key][] = [
								"instance"	=> $r,
							];
						}
					}
				} else if (is_array($rule)) {
					foreach ($rule as $p) {
						if (is_string($p)) {
							$r = $this->parseRuleFromString($p, $key);

							if ($r !== null) {
								$this->rules[$key][] = [
									"instance"	=> $r,
								];
							}
						} else if ($p instanceof ValidationRule) {
							$p->translation = $this->translation;

							$ruleParams = $p->params;
							$p->params = [
								"attribute"	=> $key
							];

							if (count($ruleParams) > 0) {
								$p->params["rule"] = $ruleParams;
							}

							$this->rules[$key][] = [
								"instance"	=> $p,
							];
						}
					}
				}
			}
		}
	}
}
