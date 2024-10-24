<?php

declare(strict_types=1);

namespace Nagyl\Rules;

use Nagyl\ValidationRule;

class BooleanRule extends ValidationRule
{
	public function validate(string $name, $value, $allValues, array $rules): bool
	{
		if (
			$value === true ||
			$value === false ||
			$value === 1 ||
			$value === 0 ||
			$value === "1" ||
			$value === "0" ||
			($this->nullable($rules) && $value === null)
		) {
			return true;
		}

		$this->message = $this->translation->get("invalid", ["attribute" => $name]);
		return false;
	}
}
