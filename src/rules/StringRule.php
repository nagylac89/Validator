<?php

declare(strict_types=1);

namespace Nagyl\Rules;

use Nagyl\ITypedRule;
use Nagyl\ValidationRule;

class StringRule extends ValidationRule implements ITypedRule
{
	private string|array|null $value;
	private bool $safe = true;

	public function __construct(bool $safe = true)
	{
		$this->safe = $safe;
	}

	public function validate(string $name, $value, $allValues, array $rules): bool
	{
		if (($this->existsRule($rules, ArrayRule::class) && is_array($value)) || is_array($value)) {
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
							return $v === null ? null : (string) $v;
						}, $val);
					}
				} else {
					if (count($value) > 0) {
						$this->value = array_map(function ($v) {
							return $v === null ? null : (string) $v;
						}, $value);
					}
				}
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
		if ($this->safe) {
			if (is_array($this->value) && count($this->value) > 0) {
				$items = [];

				foreach ($this->value as $v) {
					if (is_array($v)) {
						$items[] = array_map(function ($val) {
							return $val !== null ? htmlspecialchars($val, ENT_QUOTES, "UTF-8") : null;
						}, $v);
					} else if (is_string($v)) {
						$items[] = htmlspecialchars($v, ENT_QUOTES, "UTF-8");
					} else {
						$items[] = $v;
					}
				}

				return $items;
			} else if (is_string($this->value)) {
				return htmlspecialchars($this->value, ENT_QUOTES, "UTF-8");
			}
		}

		return $this->value;
	}
}
