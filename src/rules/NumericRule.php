<?php

declare(strict_types=1);

namespace Nagyl\Rules;

use Nagyl\ITypedRule;
use Nagyl\ValidationRule;

class NumericRule extends ValidationRule implements ITypedRule
{
	private int|float|array|null $value = null;

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
							if ($v === null) {
								return null;
							}

							if (is_numeric($v)) {
								if (is_int($v) || filter_var($v, FILTER_VALIDATE_INT) !== false) {
									return (int) $v;
								} else if (is_float($v) || filter_var($v, FILTER_VALIDATE_FLOAT) !== false) {
									return (float) $v;
								}
							}

							return null;
						}, $val);
					}
				} else if (count($value) > 0) {
					$this->value = array_map(function ($v) {
						if ($v === null) {
							return null;
						}

						if (is_numeric($v)) {
							if (is_int($v) || filter_var($v, FILTER_VALIDATE_INT) !== false) {
								return (int) $v;
							} else if (is_float($v) || filter_var($v, FILTER_VALIDATE_FLOAT) !== false) {
								return (float) $v;
							}
						}

						return null;
					}, $value);
				}
			}
			return $isValid;
		} else if (is_numeric($value)) {
			if (is_int($value) || filter_var($value, FILTER_VALIDATE_INT) !== false) {
				$this->value = (int) $value;
			} else if (is_float($value) || filter_var($value, FILTER_VALIDATE_FLOAT) !== false) {
				$this->value = (float) $value;
			}

			return true;
		} else if ($value === null && $this->nullable($rules)) {
			return true;
		} else {
			$this->message = $this->customMessage !== null ? $this->customMessage : $this->translation->get("numeric", [...$this->params, "attribute" => $name]);
		}

		return false;
	}

	public function getValue(): mixed
	{
		return $this->value;
	}
}
