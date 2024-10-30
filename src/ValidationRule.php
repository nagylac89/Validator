<?php

declare(strict_types=1);

namespace Nagyl;

use Nagyl\Rules\NullableRule;
use Nagyl\Translation;

class ValidationRule
{
	protected string $message = "";
	protected ?string $customMessage = null;
	public Translation $translation;
	public array $params = [];

	public function validate(string $name, $value, $allValues, array $rules): bool
	{
		return true;
	}

	public function getMessage(): string
	{
		return $this->message;
	}

	public function setCustomMessage(string $message): void
	{
		$this->customMessage = $message;
	}

	protected function validateInArray(string $name, array $value, $allValues, array $rules): bool
	{
		foreach ($value as $v) {
			if ($this->validate($name, $v, $allValues, $rules) === false) {
				return false;
			}
		}

		return true;
	}

	protected function nullable(array $rules): bool
	{
		$classNames = array_map(function ($r) {
			return get_class($r);
		}, $rules);

		return in_array(NullableRule::class, $classNames);
	}

	protected function existsRule(array $rules, string $ruleClass): bool
	{
		$classNames = array_map(function ($r) {
			return get_class($r);
		}, $rules);

		return in_array($ruleClass, $classNames);
	}

	protected function getRule(array $rules, string $ruleClass): ?ValidationRule
	{
		foreach ($rules as $rule) {
			if (get_class($rule) === $ruleClass) {
				return $rule;
			}
		}

		return null;
	}

	protected function allItemsAreArrays(array $array): bool
	{
		foreach ($array as $item) {
			if (!is_array($item)) {
				return false;
			}
		}

		return true;
	}
}
