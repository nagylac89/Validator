<?php

declare(strict_types=1);

namespace Nagyl;

use Nagyl\Rules\ArrayRule;
use Nagyl\Rules\BooleanRule;
use Nagyl\Rules\ContainsRule;
use Nagyl\Rules\DateRule;
use Nagyl\Rules\EqualRule;
use Nagyl\Rules\ExistsRule;
use Nagyl\Rules\FloatRule;
use Nagyl\Rules\GreaterThanOrEqualRule;
use Nagyl\Rules\GreaterThanRule;
use Nagyl\Rules\InRule;
use Nagyl\Rules\IntRule;
use Nagyl\Rules\LessThanOrEqualRule;
use Nagyl\Rules\LessThanRule;
use Nagyl\Rules\MaxLengthRule;
use Nagyl\Rules\MinLengthRule;
use Nagyl\Rules\MustRule;
use Nagyl\Rules\StopOnFailureRule;
use Nagyl\Rules\UniqueRule;
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
	private static $queryFetcher = null;
	private array $_rule = [
		"attribute" => null,
		"rules" => []
	];

	public function __construct($values, string $lang = "en")
	{
		$this->result = new ValidationResult();
		$this->values = $values;

		$this->translation = new Translation();
		$this->translation->setLang($lang);
	}

	public static function setQueryFetcher(callable $func): void
	{
		self::$queryFetcher = $func;
	}

	public static function getQueryFetcher(): ?callable
	{
		return self::$queryFetcher;
	}

	public function validate(bool $stopOnFirstError = false): bool
	{
		$this->result->isValid = true;
		$this->result->validated = true;
		$this->result->errors = [];

		if (count($this->rules) > 0) {
			foreach ($this->rules as $key => $rules) {
				$attribute = $key;
				$value = $this->getValue($attribute);

				foreach ($rules as $rule) {
					if ($rule instanceof ValidationRule) {
						if ($rule->validate($attribute, $value, $this->values, $rules) === false) {
							if (!isset($this->result->errors[$attribute])) {
								$this->result->errors[$attribute] = [];
							}

							$this->result->errors[$attribute][] = $rule->getMessage();

							if ($stopOnFirstError) {
								break;
							}
						}
					}
				}

				if (count($this->result->errors) > 0) {
					$this->result->isValid = false;

					if ($stopOnFirstError || $this->hasRule($rules, StopOnFailureRule::class)) {
						break;
					}
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

	public function attr(string $attribute): Validator
	{
		return $this->attribute($attribute);
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

	public function maxLength(int $length): Validator
	{
		$v = new MaxLengthRule($length);
		$v->translation = $this->translation;

		$this->_rule["rules"][] = $v;
		return $this;
	}

	public function boolean(): Validator
	{
		$v = new BooleanRule();
		$v->translation = $this->translation;

		$this->_rule["rules"][] = $v;
		return $this;
	}

	public function date(string|array $format = []): Validator
	{
		$formats = is_array($format) ? $format : [$format];
		$v = new DateRule($formats);
		$v->translation = $this->translation;

		$this->_rule["rules"][] = $v;
		return $this;
	}

	public function contains(string $value): Validator
	{
		$v = new ContainsRule($value);
		$v->translation = $this->translation;

		$this->_rule["rules"][] = $v;
		return $this;
	}

	public function exists(string $table, string $column, ?string $additionalFilters = null): Validator
	{
		$v = new ExistsRule($table, $column, $additionalFilters);
		$v->translation = $this->translation;

		$this->_rule["rules"][] = $v;
		return $this;
	}

	public function unique(string $table, string $column, ?string $additionalFilters = null): Validator
	{
		$v = new UniqueRule($table, $column, $additionalFilters);
		$v->translation = $this->translation;

		$this->_rule["rules"][] = $v;
		return $this;
	}

	public function when(callable $condition, callable $otherRuleFunction): Validator
	{
		if ($condition($this->values) === true) {
			$otherRuleFunction($this);
		}

		return $this;
	}

	public function eq($value): Validator
	{
		$v = new EqualRule($value);
		$v->translation = $this->translation;

		$this->_rule["rules"][] = $v;
		return $this;
	}

	public function gt($value): Validator
	{
		$v = new GreaterThanRule($value);
		$v->translation = $this->translation;

		$this->_rule["rules"][] = $v;
		return $this;
	}

	public function gte($value): Validator
	{
		$v = new GreaterThanOrEqualRule($value);
		$v->translation = $this->translation;

		$this->_rule["rules"][] = $v;
		return $this;
	}

	public function lt($value): Validator
	{
		$v = new LessThanRule($value);
		$v->translation = $this->translation;

		$this->_rule["rules"][] = $v;
		return $this;
	}

	public function lte($value): Validator
	{
		$v = new LessThanOrEqualRule($value);
		$v->translation = $this->translation;

		$this->_rule["rules"][] = $v;
		return $this;
	}

	public function stopOnFailure(): Validator
	{
		$v = new StopOnFailureRule();
		$v->translation = $this->translation;

		$this->_rule["rules"][] = $v;
		return $this;
	}

	public function must(callable $func, string $errorMsg): Validator
	{
		$v = new MustRule($func, $errorMsg);
		$v->translation = $this->translation;

		if ($this->_rule["attribute"] !== null) {
			$this->_rule["rules"][] = $v;
		} else {
			$this->rules["_mustrule"] = [$v];
		}

		return $this;
	}

	private function hasRule(array $rules, string $rule): bool
	{
		foreach ($rules as $r) {
			if ($r instanceof $rule) {
				return true;
			}
		}

		return false;
	}

	public function getValue(string $selector, $ref = null)
	{
		$parts = explode(".", $selector);
		$ref = $ref ?? $this->values;

		foreach ($parts as $index => $part) {
			if ($part === "*") {
				return $this->getWildcardValues($parts, $index, $ref);
			}
			$ref = $ref[$part] ?? null;
			if ($ref === null) {
				break;
			}
		}

		return $ref;
	}

	private function getWildcardValues(array $parts, int $index, $ref)
	{
		$values = [];
		foreach ($ref as $item) {
			$selector = join(".", array_slice($parts, $index + 1));
			$itemValues = $this->getValue($selector, $item);
			$values = array_merge($values, (array) $itemValues);
		}
		return $values;
	}
}
