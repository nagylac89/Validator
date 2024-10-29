<?php

declare(strict_types=1);

namespace Nagyl\Rules;

use Nagyl\ITypedRule;
use Nagyl\ValidationRule;

class FileRule extends ValidationRule implements ITypedRule
{
	private $value = null;

	public function validate(string $name, $value, $allValues, array $rules): bool
	{
		try {
			if (
				$this->existsRule($rules, ArrayRule::class) &&
				isset($value["name"]) &&
				is_array($value["name"]) &&
				array_is_list($value["name"])
			) {
				$files = [];

				for ($i = 0; $i < count($value["name"]); $i++) {
					if (!empty($value["name"][$i])) {
						$files[] = [
							"name" => $value["name"][$i],
							"type" => $value["type"][$i],
							"tmp_name" => $value["tmp_name"][$i],
							"error" => $value["error"][$i],
							"size" => $value["size"][$i]
						];
					}
				}

				if (count($files) === 0 && !$this->nullable($rules)) {
					$this->message = $this->customMessage ? $this->customMessage : $this->translation->get("invalid_file", [...$this->params, "attribute" => $name]);
					return false;
				} else {
					foreach ($files as $f) {
						if ($this->validate($name, $f, $allValues, $rules) === false) {
							return false;
						}
					}

					$this->value = $files;
					return true;
				}
			} else if (($value === null || (isset($value["name"]) && empty($value["name"]))) && $this->nullable($rules)) {
				return true;
			} else if (is_array($value) && isset($value["name"]) && isset($value["tmp_name"]) && is_string($value["tmp_name"]) && is_uploaded_file($value["tmp_name"])) {
				$this->value = $value;
				return true;
			}
		} catch (\Exception $e) {
		}

		$this->message = $this->customMessage ? $this->customMessage : $this->translation->get("invalid_file", [...$this->params, "attribute" => $name]);
		return false;
	}

	public function getValue(): mixed
	{
		return $this->value;
	}
}
