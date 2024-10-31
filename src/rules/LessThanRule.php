<?php

declare(strict_types=1);

namespace Nagyl\Rules;

use Nagyl\ValidationRule;

class LessThanRule extends ValidationRule
{
	public function __construct($value)
	{
		$this->params["value"] = $value;
	}

	public function validate(string $name, $value, $allValues, array $rules): bool
	{
		if (empty($value)) {
			return true;
		}

		if (($this->existsRule($rules, ArrayRule::class) && is_array($value)) || is_array($value)) {
			return $this->validateInArray($name, $value, $allValues, $rules);
		} else if (
			$this->existsRule($rules, IntRule::class) &&
			(int) $value < $this->params["value"]
		) {
			return true;
		} else if (
			$this->existsRule($rules, FloatRule::class) &&
			(float) $value < $this->params["value"]
		) {
			return true;
		} else if (
			$this->existsRule($rules, NumericRule::class) &&
			(float) $value < $this->params["value"]
		) {
			return true;
		} else if ($this->existsRule($rules, DateRule::class)) {
			$dtRule = array_filter($rules, function ($r) {
				return get_class($r) === DateRule::class;
			})[0];

			if ($dtRule->value() !== null) {
				if (is_string($this->params["value"])) {
					$dt = \DateTime::createFromFormat($dtRule->format(), $this->params["value"]);

					if ($dt !== false && $dtRule->value() < $dt) {
						return true;
					}
				} else if ($this->params["value"] instanceof \DateTime) {
					if ($dtRule->value() < $this->params["value"]) {
						return true;
					}
				}
			}
		} else if ($value < $this->params["value"]) {
			return true;
		}

		$this->message = $this->customMessage !== null ? $this->customMessage : $this->translation->get("lt", [...$this->params, "attribute" => $name]);
		return false;
	}
}
