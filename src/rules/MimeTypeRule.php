<?php

declare(strict_types=1);

namespace Nagyl\Rules;

use Nagyl\ValidationRule;

class MimeTypeRule extends ValidationRule
{
	public function __construct(array $mimeTypes)
	{
		$this->params["mimeTypes"] = $mimeTypes;
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
							$mimeType = $this->getFileMimeType($f);

							if ($mimeType === null || !in_array($mimeType, $this->params["mimeTypes"])) {
								$this->message = $this->customMessage ? $this->customMessage : $this->translation->get("mime_type", [...$this->params, "attribute" => $name]);
								return false;
							}
						}
					} else if (isset($files["name"])) {
						$mimeType = $this->getFileMimeType($files);

						if ($mimeType === null || !in_array($mimeType, $this->params["mimeTypes"])) {
							$this->message = $this->customMessage ? $this->customMessage : $this->translation->get("mime_type", [...$this->params, "attribute" => $name]);
							return false;
						}
					}
				}

				return true;
			}
		}

		return true;
	}

	private function getFileMimeType($file): ?string
	{
		$mimeType = mime_content_type($file["tmp_name"]);

		var_dump($mimeType);
		return $mimeType !== false ? strtolower($mimeType) : null;
	}

}
