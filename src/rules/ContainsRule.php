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

		if ($this->existsRule($rules, ArrayRule::class) && is_array($value)) {
			return $this->validateInArray($name, $value, $allValues, $rules);
		} else if (is_string($value) && mb_strrpos(strtolower($value), strtolower($this->params["value"])) !== false) {
			return true;
		}

		$this->message = $this->customMessage !== null ? $this->customMessage : $this->translation->get("contains", [...$this->params, "attribute" => $name]);
		return false;
	}


}
