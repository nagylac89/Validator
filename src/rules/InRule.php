<?php

declare(strict_types=1);

namespace Nagyl\Rules;

use Nagyl\ValidationRule;

class InRule extends ValidationRule
{
	public function validate(string $name, $value, $allValues, array $rules): bool
	{
		if (
			isset($this->params["rule"]) &&
			is_array($this->params["rule"]) &&
			count($this->params["rule"]) > 0 &&
			in_array($value, $this->params["rule"])
		) {
			return true;
		} else {
			$this->message = $this->translation->get("in", $this->params);
		}

		return false;
	}
}
