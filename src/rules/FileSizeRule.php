<?php

declare(strict_types=1);

namespace Nagyl\Rules;

use Nagyl\ValidationRule;

class FileSizeRule extends ValidationRule
{
	public function __construct(int $maxSizeInKilobytes)
	{
		$this->params["maxSizeInKilobytes"] = $maxSizeInKilobytes;
	}

	public function validate(string $name, $value, $allValues, array $rules): bool
	{
		if ($this->existsRule($rules, FileRule::class)) {
			$fileRule = $this->getRule($rules, FileRule::class);

			if ($fileRule !== null && $fileRule instanceof FileRule) {
				$files = $fileRule->getValue();

				if ($files !== null) {
					if (is_array($files) && array_is_list($files)) {
						foreach ($files as $f) {
							$sizeKb = $this->getFileSizeInKilobytes($f);

							if ($sizeKb > $this->params["maxSizeInKilobytes"]) {
								$params = $this->getParams();
								$this->message = $this->customMessage ? $this->customMessage : $this->translation->get("max_filesize", [...$params, "attribute" => $name]);
								return false;
							}
						}
					} else if (isset($files["size"])) {
						$sizeKb = $this->getFileSizeInKilobytes($files);

						if ($sizeKb > $this->params["maxSizeInKilobytes"]) {
							$params = $this->getParams();
							$this->message = $this->customMessage ? $this->customMessage : $this->translation->get("max_filesize", [...$params, "attribute" => $name]);
							return false;
						}
					}
				}

				return true;
			}
		}

		return true;
	}

	private function getParams(): array
	{
		$params = [
			"size" => $this->params["maxSizeInKilobytes"],
			"unit" => "KB"
		];

		if ($params["size"] > 1024) {
			$params["size"] = $params["size"] / 1024;
			$params["unit"] = "MB";
		}

		return $params;
	}

	private function getFileSizeInKilobytes($file): float
	{
		return $file["size"] / 1024;
	}
}
