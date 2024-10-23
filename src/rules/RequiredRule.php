<?php

declare(strict_types=1);

namespace Nagyl\Rules;

use Nagyl\ValidationRule;

class RequiredRule extends ValidationRule
{
	public function validate(string $name, $value, $allValues, array $params, array $rules): bool
	{
		if (
			$value === null ||
			(is_string($value) && trim($value) === "") ||
			(is_array($value) && count($value) === 0)
		) {
			$this->message = $this->translation->get("required", $params);

			return false;
		}

		return true;
	}
}
