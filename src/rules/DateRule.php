<?php

declare(strict_types=1);

namespace Nagyl\Rules;

use DateTime;
use Nagyl\ValidationRule;

class DateRule extends ValidationRule
{
	private ?DateTime $val = null;
	private string $parseFormat = "";

	public function __construct(array $formats = [])
	{
		if (count($formats) === 0) {
			$this->params["formats"] = [
				"Y-m-d H:i:s",
				"Y-m-d H:i",
				"Y-m-d"
			];
		} else {
			$this->params["formats"] = $formats;
		}
	}

	public function validate(string $name, $value, $allValues, array $rules): bool
	{
		if ($this->existsRule($rules, ArrayRule::class) && is_array($value)) {
			foreach ($value as $v) {
				if ($this->validate($name, $v, $allValues, $rules) === false) {
					return false;
				}
			}

			return true;
		} else if (is_string($value)) {
			if (count($this->params["formats"]) > 0) {
				foreach ($this->params["formats"] as $f) {
					$v = DateTime::createFromFormat($f, $value);

					if ($v !== false) {
						$this->parseFormat = $f;
						$this->val = clone $v;
						return true;
					}
				}
			}
		} else if ($value === null && $this->nullable($rules)) {
			return true;
		} else if ($value instanceof DateTime) {
			$this->val = clone $value;
			return true;
		}

		$this->message = $this->translation->get("date", ["attribute" => $name]);
		return false;
	}

	public function value(): ?DateTime
	{
		return $this->val;
	}

	public function format(): string
	{
		return $this->parseFormat;
	}
}
