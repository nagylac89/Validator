<?php

declare(strict_types=1);

namespace Nagyl\Rules;

use Nagyl\ValidationRule;

class NumericRule extends ValidationRule
{
	public function validate(string $name, $value, $allValues, array $rules): bool
	{
		if ($this->existsRule($rules, ArrayRule::class) && is_array($value)) {
			return $this->validateInArray($name, $value, $allValues, $rules);
		} else if (is_numeric($value)) {
			return true;
		} else if ($value === null && $this->nullable($rules)) {
			return true;
		} else {
			$this->message = $this->translation->get("numeric", [...$this->params, "attribute" => $name]);
		}

		return false;
	}
}
