<?php

declare(strict_types=1);

namespace Nagyl\Rules;

use Nagyl\ValidationRule;

class NullableRule extends ValidationRule
{
	public function validate(string $name, $value, $allValues, array $rules): bool
	{
		return true;
	}
}
