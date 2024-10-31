<?php

declare(strict_types=1);

namespace Nagyl\Rules;

use Nagyl\ValidationRule;

class RequiredRule extends ValidationRule
{
	public function validate(string $name, $value, $allValues, array $rules): bool
	{
		if (($this->existsRule($rules, ArrayRule::class) && is_array($value)) || (is_array($value) && count($value) > 0)) {
			if (count($value) === 0) {
				$this->message = $this->customMessage !== null ? $this->customMessage : $this->translation->get("required", [...$this->params, "attribute" => $name]);
				return false;
			}

			foreach ($value as $v) {
				if ($v === null || (is_string($v) && trim($v) === "")) {
					$this->message = $this->customMessage !== null ? $this->customMessage : $this->translation->get("required", [...$this->params, "attribute" => $name]);

					return false;
				}
			}

		} else if (
			$value === null ||
			(is_string($value) && trim($value) === "") ||
			(is_array($value) && count($value) === 0)
		) {
			$this->message = $this->customMessage !== null ? $this->customMessage : $this->translation->get("required", [...$this->params, "attribute" => $name]);

			return false;
		}

		return true;
	}
}
