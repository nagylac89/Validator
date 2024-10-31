<?php

declare(strict_types=1);

namespace Nagyl\Rules;

use Nagyl\ValidationRule;
use Nagyl\Validator;

class UniqueRule extends ValidationRule
{
	public function __construct(string $table, string $column, ?string $additionalFilters = null)
	{
		$this->params["table"] = $table;
		$this->params["column"] = $column;
		$this->params["additionalFilters"] = $additionalFilters;
	}

	public function validate(string $name, $value, $allValues, array $rules): bool
	{
		if (($this->existsRule($rules, ArrayRule::class) && is_array($value)) || is_array($value)) {
			$notEmptyValues = array_values(array_filter($value, fn($v) => $v !== null && trim($v) !== ""));
			$uniqueValues = array_values(array_unique($notEmptyValues));

			if (count($notEmptyValues) === count($uniqueValues)) {
				$qs = $this->queryString($rules, $value);

				try {
					$result = Validator::getQueryFetcher()($qs);

					if (
						is_array($result) &&
						count($result) > 0 &&
						isset($result[0]["db"]) &&
						(int) $result[0]["db"] === 0
					) {
						return true;
					}
				} catch (\Exception $ex) {
				}
			}
		} else if ($value !== null && Validator::getQueryFetcher() !== null) {
			$qs = $this->queryString($rules, $value);

			if ($qs !== null) {
				try {
					$result = Validator::getQueryFetcher()($qs);

					if (
						is_array($result) &&
						count($result) > 0 &&
						isset($result[0]["db"]) &&
						(int) $result[0]["db"] === 0
					) {
						return true;
					}
				} catch (\Exception $ex) {
				}
			}
		}

		$this->message = $this->customMessage !== null ? $this->customMessage : $this->translation->get("unique", ["attribute" => $name]);
		return false;
	}

	private function queryString(array $rules, $value): ?string
	{
		$sqlVal = null;
		$logicalOperator = "=";

		if ($value !== null) {
			if (($this->existsRule($rules, ArrayRule::class) && is_array($value)) || is_array($value)) {
				$uniqueValues = array_values(array_unique($value));

				if ($this->existsRule($rules, IntRule::class) || $this->existsRule($rules, NumericRule::class)) {
					$sqlVal = "(" . implode(",", $uniqueValues) . ")";
				} else {
					$sqlVal = "('" . implode("','", $uniqueValues) . "')";
				}

				$logicalOperator = "IN";
			} else if ($this->existsRule($rules, IntRule::class) || $this->existsRule($rules, NumericRule::class)) {
				$sqlVal = (int) $value;
			} else {
				$sqlVal = "'" . $value . "'";
			}
		}

		if ($sqlVal) {
			$qs = sprintf(
				"SELECT COUNT(*) AS db FROM %s WHERE %s %s %s",
				$this->params["table"],
				$this->params["column"],
				$logicalOperator,
				$sqlVal
			);

			if ($this->params["additionalFilters"] !== null) {
				$qs .= " " . $this->params["additionalFilters"];
			}

			return $qs;
		}

		return null;
	}
}
