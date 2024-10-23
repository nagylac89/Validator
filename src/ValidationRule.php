<?php

declare(strict_types=1);

namespace Nagyl;

use Exception;
use Nagyl\Rules\NullableRule;
use Nagyl\Translation;

class ValidationRule
{
	protected string $message = "";
	public Translation $translation;
	public array $params = [];

	public function validate(string $name, $value, $allValues, array $rules): bool
	{
		return true;
	}

	public function getMessage(): string
	{
		return $this->message;
	}

	public static function create(array $params = []): ?ValidationRule
	{
		$className = get_called_class();

		try {
			if (class_exists($className)) {
				$instance = new $className();

				if ($instance instanceof ValidationRule) {
					$instance->params = $params;

					return $instance;
				}
			}
		} catch (Exception $ex) {
		}

		return null;
	}

	protected function nullable(array $rules): bool
	{
		$classNames = array_map(function ($r) {
			return get_class($r["instance"]);
		}, $rules);

		return in_array(NullableRule::class, $classNames);
	}
}
