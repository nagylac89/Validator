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

	/**
	 * Constructor for the Validator class.
	 *
	 * @param mixed $inputValues The input values to be validated.
	 * @param array $names An optional array of names corresponding to the input values.
	 */
	public function __construct($inputValues, array $names = [])
	{
		$this->result = new ValidationResult();
		$this->inputValues = $inputValues;
		$this->names = $names;

		$this->translation = new Translation();
		$this->translation->setLang(self::$lang);
	}

	/**
	 * Sets the query fetcher callback.
	 * 
	 * The query fetcher callback is used to fetch the query result for the exists and unique rules.
	 * @param callable $func The callback function to be set. function (string $queryString): array
	 */
	public static function setQueryFetcher(callable $func): void
	{
		self::$queryFetcherCallback = $func;
	}

	/**
	 * Sets the language for the Validator.
	 *
	 * @param string $lang The language code to set. default language is "en".
	 *
	 * @return void
	 */
	public static function setLang(string $lang): void
	{
		self::$lang = $lang;
	}

	public static function getQueryFetcher(): ?callable
	{
		return self::$queryFetcherCallback;
	}

	/**
	 * Validates the data according to the defined rules.
	 *
	 * @param bool $stopOnFirstError Optional. If set to true, the validation will stop on the first encountered error. Default is false.
	 * @return bool Returns true if the data is valid, false otherwise.
	 */
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

	/**
	 * Returns the result of the validation process.
	 *
	 * @return ValidationResult The result of the validation.
	 */
	public function result(): ValidationResult
	{
		return $this->result;
	}

	/**
	 * Sets the attribute to be validated.
	 *
	 * @param string $attribute The name of the attribute.
	 * @return Validator Returns the current Validator instance.
	 */
	public function attribute(string $attribute): Validator
	{
		$this->_rule["attribute"] = $attribute;
		$this->_rule["rules"] = [];

		return $this;
	}

	/**
	 * Sets the attribute to be validated.
	 *
	 * @param string $attribute The name of the attribute.
	 * @return Validator Returns the current Validator instance.
	 */
	public function attr(string $attribute): Validator
	{
		return $this->attribute($attribute);
	}

	/**
	 * Sets the attribute to be validated.
	 *
	 * @param string $attribute The name of the attribute.
	 * @return Validator Returns the current Validator instance.
	 */
	public function property(string $attribute): Validator
	{
		return $this->attribute($attribute);
	}

	/**
	 * Sets the attribute to be validated.
	 *
	 * @param string $attribute The name of the attribute.
	 * @return Validator Returns the current Validator instance.
	 */
	public function prop(string $attribute): Validator
	{
		return $this->attribute($attribute);
	}


	/**
	 * Adds a new item to the validator.
	 *
	 * This method is responsible for adding queued validation rules. 
	 * This close the fluent assertions.
	 *
	 * @return void
	 */
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

	/**
	 * Sets a custom validation message for the previous rule.
	 *
	 * @param string $message The custom message to be used for validation.
	 * @return Validator Returns the current Validator instance.
	 */
	public function withMessage(string $message): Validator
	{
		if (count($this->_rule["rules"]) > 0) {
			$lastRule = end($this->_rule["rules"]);
			$lastRule->setCustomMessage($message);
		}

		return $this;
	}

	/**
	 * Add an string validation rule to the current attribute.
	 *
	 * @param bool $safe Optional. If true, use xss protection for this attribute when call the validatedValues method.
	 * @return Validator Returns the current Validator instance.
	 */
	public function string(bool $safe = true): Validator
	{
		$v = new StringRule($safe);
		$v->translation = $this->translation;

		$this->_rule["rules"][] = $v;
		return $this;
	}

	/**
	 * Add an email validation rule to the current attribute.
	 *
	 * @return Validator Returns the current Validator instance.
	 */
	public function email(): Validator
	{
		$v = new EmailRule();
		$v->translation = $this->translation;

		$this->_rule["rules"][] = $v;
		return $this;
	}

	/**
	 * Marks the field as required.
	 *
	 * @return Validator Returns the current instance of the Validator for method chaining.
	 */
	public function required(): Validator
	{
		$v = new RequiredRule();
		$v->translation = $this->translation;

		$this->_rule["rules"][] = $v;
		return $this;
	}

	/**
	 * Marks the field as nullable, allowing it to be null.
	 *
	 * @return Validator Returns the current Validator instance.
	 */
	public function nullable(): Validator
	{
		$v = new NullableRule();
		$v->translation = $this->translation;

		$this->_rule["rules"][] = $v;
		return $this;
	}

	/**
	 * Add array validation rule to the current attribute.
	 *
	 * @return Validator Returns the current Validator instance.
	 */
	public function array(): Validator
	{
		$v = new ArrayRule();
		$v->translation = $this->translation;

		$this->_rule["rules"][] = $v;
		return $this;
	}

	/**
	 * Add float validation rule to the current attribute.
	 *
	 * @return Validator Returns the current Validator instance.
	 */
	public function float(): Validator
	{
		$v = new FloatRule();
		$v->translation = $this->translation;

		$this->_rule["rules"][] = $v;
		return $this;
	}

	/**
	 * Add integer validation rule to the current attribute.
	 *
	 * @return Validator Returns the current Validator instance for method chaining.
	 */
	public function int(): Validator
	{
		$v = new IntRule();
		$v->translation = $this->translation;

		$this->_rule["rules"][] = $v;
		return $this;
	}

	/**
	 * Add numeric validation rule to the current attribute.
	 *
	 * @return Validator Returns the current Validator instance.
	 */
	public function numeric(): Validator
	{
		$v = new NumericRule();
		$v->translation = $this->translation;

		$this->_rule["rules"][] = $v;
		return $this;
	}

	/**
	 * Validates if the value is within the given array of values.
	 *
	 * @param array $values The array of values to check against.
	 * @return Validator Returns the current Validator instance.
	 */
	public function in(array $values): Validator
	{
		$v = new InRule($values);
		$v->translation = $this->translation;

		$this->_rule["rules"][] = $v;
		return $this;
	}

	/**
	 * Sets the minimum length requirement for validation.
	 *
	 * @param int $length The minimum length to be set.
	 * @return Validator Returns the current Validator instance.
	 */
	public function minLength(int $length): Validator
	{
		$v = new MinLengthRule($length);
		$v->translation = $this->translation;

		$this->_rule["rules"][] = $v;
		return $this;
	}

	/**
	 * Sets the maximum length for validation.
	 *
	 * @param int $length The maximum length allowed.
	 * @return Validator Returns the current Validator instance.
	 */
	public function maxLength(int $length): Validator
	{
		$v = new MaxLengthRule($length);
		$v->translation = $this->translation;

		$this->_rule["rules"][] = $v;
		return $this;
	}

	/**
	 * Add boolean validation for current attribute.
	 *
	 * @return Validator Returns the current Validator instance.
	 */
	public function boolean(): Validator
	{
		$v = new BooleanRule();
		$v->translation = $this->translation;

		$this->_rule["rules"][] = $v;
		return $this;
	}

	/**
	 * Validates that the input is a date matching the specified format(s).
	 *
	 * @param string|array $format The date format(s) to validate against. Can be a single format as a string or multiple formats as an array. defaults: ["Y-m-d", "Y-m-d H:i", "Y-m-d H:i:s"]
	 * @return Validator Returns the current Validator instance.
	 */
	public function date(string|array $format = []): Validator
	{
		$formats = is_array($format) ? $format : [$format];
		$v = new DateRule($formats);
		$v->translation = $this->translation;

		$this->_rule["rules"][] = $v;
		return $this;
	}

	/**
	 * Checks if the current attribute value is contained within the validator.
	 *
	 * @param string $value The value to check for containment.
	 * @return Validator Returns the current Validator instance.
	 */
	public function contains(string $value): Validator
	{
		$v = new ContainsRule($value);
		$v->translation = $this->translation;

		$this->_rule["rules"][] = $v;
		return $this;
	}

	/**
	 * Checks if a record exists in the specified table and column with optional additional filters.
	 * For this rule to work, need to configure the query fetcher callback with this method setQueryFetcher.
	 *
	 * @param string $table The name of the table to check.
	 * @param string $column The name of the column to check.
	 * @param string|null $additionalFilters Optional additional SQL filters to apply.
	 * @return Validator Returns the current Validator instance.
	 */
	public function exists(string $table, string $column, ?string $additionalFilters = null): Validator
	{
		$v = new ExistsRule($table, $column, $additionalFilters);
		$v->translation = $this->translation;

		$this->_rule["rules"][] = $v;
		return $this;
	}

	/**
	 * Ensures that the value is unique in the specified database table and column.
	 * For this rule to work, need to configure the query fetcher callback with this method setQueryFetcher.
	 *
	 * @param string $table The name of the database table to check.
	 * @param string $column The name of the column in the table to check.
	 * @param string|null $additionalFilters Optional additional SQL filters to apply to the uniqueness check.
	 * @return Validator Returns the current Validator instance.
	 */
	public function unique(string $table, string $column, ?string $additionalFilters = null): Validator
	{
		$v = new UniqueRule($table, $column, $additionalFilters);
		$v->translation = $this->translation;

		$this->_rule["rules"][] = $v;
		return $this;
	}

	/**
	 * Applies a validation rule conditionally.
	 *
	 * This method allows you to apply a validation rule only when a specified condition is met.
	 *
	 * @param callable $condition A callable that returns a boolean indicating whether the $otherRuleFunction should be applied.
	 * @param callable $otherRuleFunction A callable that contains the validation rule to be applied if the $condition returns true.
	 * @return Validator Returns the current Validator instance for method chaining.
	 */
	public function when(callable $condition, callable $otherRuleFunction): Validator
	{
		if ($condition($this->inputValues) === true) {
			$otherRuleFunction($this);
		}

		return $this;
	}

	/**
	 * Validates if the given value is equal to a predefined value.
	 *
	 * @param mixed $value The value to be compared.
	 * @return Validator Returns the current Validator instance.
	 */
	public function eq($value): Validator
	{
		$v = new EqualRule($value);
		$v->translation = $this->translation;

		$this->_rule["rules"][] = $v;
		return $this;
	}

	/**
	 * Validates that the given value is greater than a specified threshold.
	 *
	 * @param mixed $value The value to be validated.
	 * @return Validator Returns the current Validator instance.
	 */
	public function gt($value): Validator
	{
		$v = new GreaterThanRule($value);
		$v->translation = $this->translation;

		$this->_rule["rules"][] = $v;
		return $this;
	}

	/**
	 * Validates that the given value is greater than or equal to a specified threshold.
	 *
	 * @param mixed $value The value to be validated.
	 * @return Validator Returns the current Validator instance.
	 */
	public function gte($value): Validator
	{
		$v = new GreaterThanOrEqualRule($value);
		$v->translation = $this->translation;

		$this->_rule["rules"][] = $v;
		return $this;
	}

	/**
	 * Validates that the given value is less than a specified threshold.
	 *
	 * @param mixed $value The value to be validated.
	 * @return Validator Returns the current Validator instance.
	 */
	public function lt($value): Validator
	{
		$v = new LessThanRule($value);
		$v->translation = $this->translation;

		$this->_rule["rules"][] = $v;
		return $this;
	}

	/**
	 * Validates that the given value is less than or equal to a specified value.
	 *
	 * @param mixed $value The value to be validated.
	 * @return Validator Returns the current Validator instance.
	 */
	public function lte($value): Validator
	{
		$v = new LessThanOrEqualRule($value);
		$v->translation = $this->translation;

		$this->_rule["rules"][] = $v;
		return $this;
	}

	/**
	 * Stops the validation process on the first failure, on attribute level.
	 *
	 * @return Validator Returns the current Validator instance.
	 */
	public function stopOnFailure(): Validator
	{
		$v = new StopOnFailureRule();
		$v->translation = $this->translation;

		$this->_rule["rules"][] = $v;
		return $this;
	}

	/**
	 * Adds a custom validation rule to the validator.
	 *
	 * @param callable $func The validation function that must return a boolean. The function gets all values as a parameter. function($inputValues): bool
	 * @param string $errorMsg The error message to display if the validation fails.
	 * @return Validator Returns the current Validator instance.
	 */
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

	/**
	 * A file validation rule. This rule is used to validate file uploads.
	 *
	 * @return Validator Returns the current Validator instance.
	 */
	public function file(): Validator
	{
		$v = new FileRule();
		$v->translation = $this->translation;

		$this->_rule["rules"][] = $v;
		return $this;
	}

	/**
	 * Validates that the file size does not exceed the specified maximum size.
	 *
	 * @param int $maxSizeInKilobytes The maximum file size allowed in kilobytes.
	 * @return Validator Returns the current Validator instance.
	 */
	public function fileSize(int $maxSizeInKilobytes): Validator
	{
		$v = new FileSizeRule($maxSizeInKilobytes);
		$v->translation = $this->translation;

		$this->_rule["rules"][] = $v;
		return $this;
	}

	/**
	 * Validates the file extension against a list of allowed extensions.
	 *
	 * @param array $extensions An array of allowed file extensions. e.g. ["jpg", "png", "gif"]
	 * @return Validator Returns the current Validator instance.
	 */
	public function fileExt(array $extensions): Validator
	{
		$v = new FileExtensionRule($extensions);
		$v->translation = $this->translation;

		$this->_rule["rules"][] = $v;
		return $this;
	}

	/**
	 * Validates the MIME type of a file.
	 *
	 * @param array $mimeTypes An array of allowed MIME types. e.g. ["image/jpeg", "image/png"]
	 * @return Validator Returns the current Validator instance.
	 */
	public function mimeType(array $mimeTypes): Validator
	{
		$v = new MimeTypeRule($mimeTypes);
		$v->translation = $this->translation;

		$this->_rule["rules"][] = $v;
		return $this;
	}

	/**
	 * Get the validated values.
	 *
	 * This method returns an array of values that have been validated.
	 * The array is structured is same as the input values and validation keys.
	 * If the inputValues contains not validated values, they will not be included in the returned array.
	 * The array values will be of specified types according to the validation rules.
	 * 		e.g.: if the input value is "1" and the validation rule is int(), the validated value will be 1.
	 *
	 * @return array The array of validated values.
	 */
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

	private function hasRule(array $rules, string $rule): bool
	{
		foreach ($rules as $r) {
			if ($r instanceof $rule) {
				return true;
			}
		}

		return false;
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
