<?php

declare(strict_types=1);

namespace Nagyl\Rules;

use Nagyl\ValidationRule;

class IntRule extends ValidationRule
{
	public function validate(string $name, $value, $allValues, array $rules): bool
	{
		if ($this->existsRule($rules, ArrayRule::class) && is_array($value)) {
			return $this->validateInArray($name, $value, $allValues, $rules);
		} else if (is_int($value)) {
			return true;
		} else if (is_string($value) && is_numeric($value) && (string) (int) $value === $value) {
			return true;
		} else if ($value === null && $this->nullable($rules)) {
			return true;
		} else {
			$this->message = $this->translation->get("int", [...$this->params, "attribute" => $name]);
		}

		return false;
	}
}
