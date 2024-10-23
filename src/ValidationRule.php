<?php

declare(strict_types=1);

namespace Nagyl;

use Nagyl\Rules\NullableRule;
use Nagyl\Translation;

class ValidationRule
{
	protected string $message = "";
	public Translation $translation;

	public function validate(string $name, $value, $allValues, array $params, array $rules): bool
	{
		return true;
	}

	public function getMessage(): string
	{
		return $this->message;
	}

	protected function nullable(array $rules): bool
	{
		return in_array(NullableRule::class, array_column($rules, "class"));
	}
}
