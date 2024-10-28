<?php

declare(strict_types=1);

namespace Nagyl\Rules;

use Nagyl\ValidationRule;

class MinLengthRule extends ValidationRule
{
	public function __construct(int $length)
	{
		$this->params["length"] = $length;
	}

	public function validate(string $name, $value, $allValues, array $rules): bool
	{
		if ($this->existsRule($rules, ArrayRule::class) && is_array($value)) {
			return $this->validateInArray($name, $value, $allValues, $rules);
		} else if ($value !== null && is_string($value) && mb_strlen($value) < $this->params["length"]) {
			$this->message = $this->customMessage !== null ? $this->customMessage : $this->translation->get("string_min_length", [...$this->params, "attribute" => $name]);
			return false;
		}

		return true;
	}
}
