<?php

declare(strict_types=1);

namespace Nagyl\Rules;

use Nagyl\ITypedRule;
use Nagyl\ValidationRule;

class BooleanRule extends ValidationRule implements ITypedRule
{
	private bool|array|null $value = null;

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
						foreach ($val as $v) {
							if ($v === null) {
								$this->value[] = null;
							}

							if ($v === true || $v === false || $v === 1 || $v === 0 || $v === "1" || $v === "0") {
								$this->value[] = $v === true || $v === 1 || $v === "1";
							}
						}
					}
				} else if (count($value) > 0) {
					foreach ($value as $v) {
						if ($v === null) {
							$this->value[] = null;
						}

						if ($v === true || $v === false || $v === 1 || $v === 0 || $v === "1" || $v === "0") {
							$this->value[] = $v === true || $v === 1 || $v === "1";
						}
					}
				}
			}

			return $isValid;
		} else if (
			$value === true ||
			$value === false ||
			$value === 1 ||
			$value === 0 ||
			$value === "1" ||
			$value === "0" ||
			($this->nullable($rules) && $value === null)
		) {
			if ($value === true || $value === 1 || $value === "1") {
				$this->value = true;
			} else if ($value === false || $value === 0 || $value === "0") {
				$this->value = false;
			}

			return true;
		}

		$this->message = $this->customMessage !== null ? $this->customMessage : $this->translation->get("invalid", ["attribute" => $name]);
		return false;
	}

	public function getValue(): mixed
	{
		return $this->value;
	}
}
