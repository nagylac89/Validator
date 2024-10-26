<?php

declare(strict_types=1);

namespace Nagyl\Rules;

use Nagyl\ValidationRule;

class EqualRule extends ValidationRule
{
	public function __construct($value)
	{
		$this->params["value"] = $value;
	}

	public function validate(string $name, $value, $allValues, array $rules): bool
	{
		if ($value === $this->params["value"]) {
			return true;
		} else {
			$this->message = $this->translation->get("eq", [...$this->params, "attribute" => $name]);
		}

		return false;
	}
}