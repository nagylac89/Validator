<?php

declare(strict_types=1);

namespace Nagyl\Rules;

use DateTime;
use Nagyl\ITypedRule;
use Nagyl\ValidationRule;

class DateRule extends ValidationRule implements ITypedRule
{
	private DateTime|array|null $val = null;
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
			$isValid = $this->validateInArray($name, $value, $allValues, $rules);

			if ($isValid && count($value) > 0) {
				$this->val = [];

				foreach ($value as $v) {
					if ($v === null) {
						$this->val[] = null;
					}

					if (is_string($v)) {
						foreach ($this->params["formats"] as $f) {
							$dt = DateTime::createFromFormat($f, $v);

							if ($dt !== false) {
								$this->val[] = clone $dt;
							}
						}
					} else if ($v instanceof DateTime) {
						$this->val[] = $v;
					}
				}
			}

			return $isValid;
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

		$this->message = $this->customMessage !== null ? $this->customMessage : $this->translation->get("date", ["attribute" => $name]);
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

	public function getValue(): mixed
	{
		return $this->val;
	}
}
