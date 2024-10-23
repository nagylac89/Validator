<?php

declare(strict_types=1);

namespace Nagyl\Rules;

use Nagyl\ValidationRule;

class NumericRule extends ValidationRule
{
	public function validate(string $name, $value, $allValues, array $rules): bool
	{
		if (is_numeric($value)) {
			return true;
		} else if ($value === null && $this->nullable($rules)) {
			return true;
		} else {
			$this->message = $this->translation->get("numeric", $this->params);
		}

		return false;
	}
}
