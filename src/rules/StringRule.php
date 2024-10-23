<?php

declare(strict_types=1);

namespace Nagyl\Rules;

use Nagyl\ValidationRule;

class StringRule extends ValidationRule
{
	public function validate(string $name, $value, $allValues, array $rules): bool
	{
		if (is_string($value) || ($value === null && $this->nullable($rules))) {
			return true;
		} else {
			$this->message = $this->translation->get("string", $this->params);
		}

		return false;
	}
}
