<?php

declare(strict_types=1);

namespace Nagyl\Rules;

use DateTime;
use Nagyl\ValidationRule;

class DateRule extends ValidationRule
{
	private ?DateTime $val = null;

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
		if (is_string($value)) {
			if (count($this->params["formats"]) > 0) {
				foreach ($this->params["formats"] as $f) {
					$v = DateTime::createFromFormat($f, $value);

					if ($v !== false) {
						$this->val = clone $v;
						return true;
					}
				}
			}
		} else if ($value === null && $this->nullable($rules)) {
			return true;
		} else if ($value instanceof DateTime) {
			return true;
		}

		return false;
	}
}
