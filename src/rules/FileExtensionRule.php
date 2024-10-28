<?php

declare(strict_types=1);

namespace Nagyl\Rules;

use Nagyl\ValidationRule;

class FileExtensionRule extends ValidationRule
{
	public function __construct(array $enabledExtensions)
	{
		$this->params["extensions"] = $enabledExtensions;
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
							$ext = $this->getFileExtension($f);

							if ($ext === null || !in_array($ext, $this->params["extensions"])) {
								$this->message = $this->customMessage ? $this->customMessage : $this->translation->get("file_extension", [...$this->params, "attribute" => $name]);
								return false;
							}
						}
					} else if (isset($files["name"])) {
						$ext = $this->getFileExtension($files);

						if ($ext === null || !in_array($ext, $this->params["extensions"])) {
							$this->message = $this->customMessage ? $this->customMessage : $this->translation->get("file_extension", [...$this->params, "attribute" => $name]);
							return false;
						}
					}
				}

				return true;
			}
		}

		return true;
	}

	private function getFileExtension($file): ?string
	{
		$ext = pathinfo($file["name"], PATHINFO_EXTENSION);
		return $ext ? strtolower($ext) : null;
	}

}
