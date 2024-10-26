<?php

declare(strict_types=1);

namespace Nagyl;

class ValidationResult
{
	public bool $validated = false;
	public bool $isValid = true;
	public array $errors = [];
}
