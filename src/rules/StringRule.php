<?php

declare(strict_types=1);

namespace Nagyl\Rules;

use Nagyl\ValidationRule;

class StringRule extends ValidationRule
{
	public function validate(string $name, $value, $allValues, array $rules): bool
	{
		if ($this->existsRule($rules, ArrayRule::class) && is_array($value)) {
			return $this->validateInArray($name, $value, $allValues, $rules);
		} else if (is_string($value) || ($value === null && $this->nullable($rules))) {
			return true;
		} else {
			$this->message = $this->customMessage !== null ? $this->customMessage : $this->translation->get("string", ["attribute" => $name]);
		}

		return false;
	}
}
