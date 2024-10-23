<?php

declare(strict_types=1);

namespace Nagyl\Rules;

use Nagyl\ValidationRule;

class FloatRule extends ValidationRule
{
	public function validate(string $name, $value, $allValues, array $params, array $rules): bool
	{
		if (is_float($value)) {
			return true;
		} else if (is_string($value) && is_numeric($value) && (string)(float)$value === $value) {
			return true;
		} else if ($value === null && $this->nullable($rules)) {
			return true;
		} else {
			$this->message = $this->translation->get("float", $params);
		}

		return false;
	}
}
