<?php

declare(strict_types=1);

namespace Nagyl;

use Nagyl\Rules\ArrayRule;
use Nagyl\Rules\BooleanRule;
use Nagyl\Rules\ContainsRule;
use Nagyl\Rules\DateRule;
use Nagyl\Rules\EmailRule;
use Nagyl\Rules\EqualRule;
use Nagyl\Rules\ExistsRule;
use Nagyl\Rules\FileExtensionRule;
use Nagyl\Rules\FileRule;
use Nagyl\Rules\FileSizeRule;
use Nagyl\Rules\FloatRule;
use Nagyl\Rules\GreaterThanOrEqualRule;
use Nagyl\Rules\GreaterThanRule;
use Nagyl\Rules\InRule;
use Nagyl\Rules\IntRule;
use Nagyl\Rules\LessThanOrEqualRule;
use Nagyl\Rules\LessThanRule;
use Nagyl\Rules\MaxLengthRule;
use Nagyl\Rules\MimeTypeRule;
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
	private $inputValues;
	private Translation $translation;
	private static $queryFetcherCallback = null;
	private array $_rule = [
		"attribute" => null,
		"rules" => []
	];

	private $names = [];
	private static $lang = "en";

	public function __construct($inputValues, array $names = [])
	{
		$this->result = new ValidationResult();
		$this->inputValues = $inputValues;
		$this->names = $names;

		$this->translation = new Translation();
		$this->translation->setLang(self::$lang);
	}

	public static function setQueryFetcher(callable $func): void
	{
		self::$queryFetcherCallback = $func;
	}

	public static function setLang(string $lang): void
	{
		self::$lang = $lang;
	}

	public static function getQueryFetcher(): ?callable
	{
		return self::$queryFetcherCallback;
	}

	public function validate(bool $stopOnFirstError = false): bool
	{
		$this->result->isValid = true;
		$this->result->validated = true;
		$this->result->errors = [];

		if (count($this->rules) > 0) {
			foreach ($this->rules as $key => $rules) {
				$attribute = $key;
				$value = $this->getValue($attribute, $this->inputValues);

				foreach ($rules as $rule) {
					if ($rule instanceof ValidationRule) {
						$attributeDisplayName = $this->getAttributeDisplayName($attribute);

						if ($rule->validate($attributeDisplayName, $value, $this->inputValues, $rules) === false) {
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

	public function property(string $attribute): Validator
	{
		return $this->attribute($attribute);
	}

	public function prop(string $attribute): Validator
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

	public function withMessage(string $message): Validator
	{
		if (count($this->_rule["rules"]) > 0) {
			$lastRule = end($this->_rule["rules"]);
			$lastRule->setCustomMessage($message);
		}

		return $this;
	}

	public function string(bool $safe = true): Validator
	{
		$v = new StringRule($safe);
		$v->translation = $this->translation;

		$this->_rule["rules"][] = $v;
		return $this;
	}

	public function email(): Validator
	{
		$v = new EmailRule();
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
		if ($condition($this->inputValues) === true) {
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

	public function file(): Validator
	{
		$v = new FileRule();
		$v->translation = $this->translation;

		$this->_rule["rules"][] = $v;
		return $this;
	}

	public function fileSize(int $maxSizeInKilobytes): Validator
	{
		$v = new FileSizeRule($maxSizeInKilobytes);
		$v->translation = $this->translation;

		$this->_rule["rules"][] = $v;
		return $this;
	}

	public function fileExt(array $extensions): Validator
	{
		$v = new FileExtensionRule($extensions);
		$v->translation = $this->translation;

		$this->_rule["rules"][] = $v;
		return $this;
	}

	public function mimeType(array $mimeTypes): Validator
	{
		$v = new MimeTypeRule($mimeTypes);
		$v->translation = $this->translation;

		$this->_rule["rules"][] = $v;
		return $this;
	}

	public function validatedValues(): array
	{
		$model = [];
		$oneLevelModel = [];
		$hasItem = false;
		$selector = "";

		foreach ($this->rules as $rk => $attributeRules) {
			$typedRule = $this->getTypedRule($attributeRules);

			if ($typedRule !== null) {
				$oneLevelModel[$rk] = $typedRule->getValue();
				$hasItem = true;
			}
		}

		if ($hasItem) {
			foreach ($oneLevelModel as $selector => $value) {
				$parts = explode(".", $selector);
				$ref = &$model;

				if (count($parts) === 1) {
					$model[$selector] = $value;
				} else {
					$this->typeValueParser($ref, $value, $parts);
				}
			}
		}

		return $model;
	}

	private function typeValueParser(&$ref, $value, array $parts)
	{
		foreach ($parts as $index => $part) {
			if ($part === "*") {
				for ($i = 0; $i < count($value); $i++) {
					if (!isset($ref[$i])) {
						$ref[$i] = [];
					}

					$this->typeValueParser($ref[$i], $value[$i], array_slice($parts, $index + 1));
				}

				break;
			}

			if ($index === count($parts) - 1) {
				$ref[$part] = $value;
			} else {
				if (!isset($ref[$part])) {
					$ref[$part] = [];
				}

				$ref = &$ref[$part];
			}
		}
	}

	private function existsRuleDefinitionByStartsWith(string $s, ?array $ruleKeys): bool
	{
		if ($ruleKeys === null) {
			$ruleKeys = array_keys($this->rules);
		}

		return count(array_filter($ruleKeys, function ($ruleKey) use ($s) {
			return strncmp($ruleKey, $s, strlen($s)) === 0;
		})) > 0;
	}

	private function getTypedRule(array $rules): ?ITypedRule
	{
		foreach ($rules as $rule) {
			if ($rule instanceof ITypedRule) {
				return $rule;
			}
		}

		return null;
	}


	private function getAttributeDisplayName(string $attribute): string
	{
		return $this->names[$attribute] ?? $attribute;
	}

	private function getValue(string $selector, $ref = null)
	{
		$parts = explode(".", $selector);
		$ref = $ref ?? $this->inputValues;

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

			if (is_array($itemValues)) {
				$values[] = $itemValues;
			} else {
				$values[] = $itemValues;
			}
		}
		return $values;
	}
}
