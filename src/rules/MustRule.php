<?php

declare(strict_types=1);

namespace Nagyl\Rules;

use Nagyl\ValidationRule;

class MustRule extends ValidationRule
{
	private $func;
	private string $errorMsg;

	public function __construct(callable $func, string $errorMsg)
	{
		$this->func = $func;
		$this->errorMsg = $errorMsg;
	}

	public function validate(string $name, $value, $allValues, array $rules): bool
	{
		$succeed = false;

		if (is_callable($this->func)) {
			$func = $this->func;

			$succeed = $func($allValues);
		}

		if (!$succeed) {
			$this->message = $this->errorMsg;
		}

		return $succeed;
	}
}