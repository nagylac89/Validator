<?php

declare(strict_types=1);

namespace Nagyl\Rules;

use Nagyl\ITypedRule;
use Nagyl\ValidationRule;

class IntRule extends ValidationRule implements ITypedRule
{
	private int|array|null $value = null;

	public function validate(string $name, $value, $allValues, array $rules): bool
	{
		if ($this->existsRule($rules, ArrayRule::class) && is_array($value)) {
			$isValid = false;
			$allItemsAreArrays = $this->allItemsAreArrays($value);

			if ($allItemsAreArrays) {
				foreach ($value as $v) {
					$isValid = $this->validateInArray($name, $v, $allValues, $rules);
					if (!$isValid) {
						break;
					}
				}
			} else {
				$isValid = $this->validateInArray($name, $value, $allValues, $rules);
			}

			if ($isValid) {
				$this->value = [];

				if ($allItemsAreArrays) {
					foreach ($value as $val) {
						$this->value[] = array_map(function ($v) {
							return $v === null ? null : (int) $v;
						}, $val);
					}
				} else if (count($value) > 0) {
					$this->value = array_map(function ($v) {
						return $v === null ? null : (int) $v;
					}, $value);
				}
			}

			return $isValid;
		} else if (is_array($value)) {
			$isValid = $this->validateInArray($name, $value, $allValues, $rules);

			if ($isValid) {
				if (count($value) === 0) {
					$this->value = [];
				} else {
					$this->value = array_map(function ($v) {
						return $v === null ? null : (int) $v;
					}, $value);
				}
			}

			return $isValid;
		} else if (is_int($value)) {
			$this->value = $value;
			return true;
		} else if (is_string($value) && is_numeric($value) && (string) (int) $value === $value) {
			$this->value = (int) $value;
			return true;
		} else if ($value === null && $this->nullable($rules)) {
			return true;
		} else {
			$this->message = $this->customMessage !== null ? $this->customMessage : $this->translation->get("int", [...$this->params, "attribute" => $name]);
		}

		return false;
	}

	public function getValue(): mixed
	{
		return $this->value;
	}
}
