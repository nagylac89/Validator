<?php

declare(strict_types=1);

namespace Nagyl\Rules;

use Nagyl\ITypedRule;
use Nagyl\ValidationRule;

class StringRule extends ValidationRule implements ITypedRule
{
	private string|array|null $value;

	public function validate(string $name, $value, $allValues, array $rules): bool
	{
		if ($this->existsRule($rules, ArrayRule::class) && is_array($value)) {
			$isValid = $this->validateInArray($name, $value, $allValues, $rules);

			if ($isValid && count($value) > 0) {
				$this->value = array_map(function ($v) {
					return $v === null ? null : (string) $v;
				}, array_filter($value, fn($v) => is_string($v) || $v === null));
			}

			return $isValid;
		} else if (is_string($value) || ($value === null && $this->nullable($rules))) {
			$this->value = $value;
			return true;
		} else {
			$this->message = $this->customMessage !== null ? $this->customMessage : $this->translation->get("string", ["attribute" => $name]);
		}

		return false;
	}

	public function getValue(): mixed
	{
		return $this->value;
	}
}
