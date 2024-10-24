<?php

declare(strict_types=1);

namespace Nagyl\Rules;

use Nagyl\ValidationRule;

class InRule extends ValidationRule
{
	public function __construct(array $values)
	{
		$this->params["values"] = $values;
	}

	public function validate(string $name, $value, $allValues, array $rules): bool
	{
		if (
			isset($this->params["values"]) &&
			is_array($this->params["values"]) &&
			count($this->params["values"]) > 0 &&
			in_array($value, $this->params["values"])
		) {
			return true;
		} else {
			$this->message = $this->translation->get("in", [...$this->params, "attribute" => $name]);
		}

		return false;
	}
}