<?php

declare(strict_types=1);

namespace Nagyl\Rules;

use Nagyl\ValidationRule;
use Nagyl\Validator;

class ExistsRule extends ValidationRule
{
	public function __construct(string $table, string $column, ?string $additionalFilters = null)
	{
		$this->params["table"] = $table;
		$this->params["column"] = $column;
		$this->params["additionalFilters"] = $additionalFilters;
	}

	public function validate(string $name, $value, $allValues, array $rules): bool
	{
		$qs = $this->queryString($rules, $value);

		if ($qs !== null && Validator::getQueryFetcher() !== null) {
			try {
				$result = Validator::getQueryFetcher()($qs);

				if (
					is_array($result) &&
					count($result) > 0 &&
					isset($result[0]["db"]) &&
					(int) $result[0]["db"] > 0
				) {
					return true;
				}
			} catch (\Exception $ex) {
			}
		}

		return false;
	}

	private function queryString(array $rules, $value): ?string
	{
		$sqlVal = null;

		if ($value !== null) {
			if ($this->existsRule($rules, IntRule::class) || $this->existsRule($rules, NumericRule::class)) {
				$sqlVal = (int) $value;
			} else {
				$sqlVal = "'" . $value . "'";
			}
		}

		if ($sqlVal) {
			$qs = "select count(*) as db from " . $this->params["table"];
			$qs .= " where " . $this->params["column"];
			$qs .= " = ";
			$qs .= $sqlVal;

			if ($this->params["additionalFilters"] !== null) {
				$qs .= " " . $this->params["additionalFilters"];
			}

			return $qs;
		}

		return null;
	}
}
