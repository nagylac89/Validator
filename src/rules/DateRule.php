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
			$isValid = false;
			$allItemsAreArrays = $this->allItemsAreArrays($value);

			if ($allItemsAreArrays) {
				foreach ($value as $v) {
					$isValid = $this->validateInArray($name, $v, $allValues, $rules);
					if (!$isValid) {
						break;
					}
				}
			} else {
				$isValid = $this->validateInArray($name, $value, $allValues, $rules);
			}

			if ($isValid) {
				$this->val = [];

				if ($allItemsAreArrays) {
					foreach ($value as $val) {
						foreach ($val as $v) {
							$this->val[] = $this->getDate($v);
						}
					}
				} else if (count($value) > 0) {
					foreach ($value as $v) {
						$this->val[] = $this->getDate($v);
					}
				}
			}

			return $isValid;
		} else if ($value === null && $this->nullable($rules)) {
			return true;
		} else {
			$date = $this->getDate($value);

			if ($date !== null) {
				$this->val = clone $date;
				return true;
			}
		}

		$this->message = $this->customMessage !== null ? $this->customMessage : $this->translation->get("date", ["attribute" => $name]);
		return false;
	}

	private function getDate($val): ?DateTime
	{
		if (is_string($val)) {
			foreach ($this->params["formats"] as $f) {
				$dt = DateTime::createFromFormat($f, $val);

				if ($dt !== false) {
					$this->parseFormat = $f;

					if (
						strpos($f, "H") === false &&
						strpos($f, "i") === false &&
						strpos($f, "s") === false
					) {
						$dt->setTime(0, 0, 0, 0);
					}

					return clone $dt;
				}
			}
		} else if ($val instanceof DateTime) {
			return $val;
		}

		return null;
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
