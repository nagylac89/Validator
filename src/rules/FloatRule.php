<?php

declare(strict_types=1);

namespace Nagyl\Rules;

use Nagyl\ValidationRule;

class FloatRule extends ValidationRule
{
	public function validate(string $name, $value, $allValues, array $rules): bool
	{
		if ($this->existsRule($rules, ArrayRule::class) && is_array($value)) {
			foreach ($value as $v) {
				if (!$this->validate($name, $v, $allValues, $rules)) {
					return false;
				}
			}

			return true;
		} else if (is_float($value)) {
			return true;
		} else if (is_string($value) && is_numeric($value) && (string) (float) $value === $value) {
			return true;
		} else if ($value === null && $this->nullable($rules)) {
			return true;
		} else {
			$this->message = $this->translation->get("float", ["attribute" => $name]);
		}

		return false;
	}
}
