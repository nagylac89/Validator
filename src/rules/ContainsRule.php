<?php

declare(strict_types=1);

namespace Nagyl\Rules;

use Nagyl\ValidationRule;

class ContainsRule extends ValidationRule
{

	public function __construct(string $value)
	{
		$this->params["value"] = $value;
	}

	public function validate(string $name, $value, $allValues, array $rules): bool
	{
		if (empty($this->params["value"])) {
			return true;
		}

		if (is_string($value) && mb_strrpos(strtolower($value), strtolower($this->params["value"]))) {
			return true;
		}

		$this->message = $this->translation->get("contains", [...$this->params, "attribute" => $name]);
		return false;
	}
}