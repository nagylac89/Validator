<?php

declare(strict_types=1);

namespace Nagyl\Rules;

use Nagyl\ValidationRule;

class ArrayRule extends ValidationRule
{
	public function validate(string $name, $value, $allValues, array $rules): bool
	{
		if (is_array($value)) {
			return true;
		} else if ($value === null && $this->nullable($rules)) {
			return true;
		}

		$this->message = $this->customMessage !== null ? $this->customMessage : $this->translation->get("array", ["attribute" => $name]);
		return false;
	}
}
