<?php

declare(strict_types=1);

namespace Nagyl;

interface ITypedRule
{
	public function getValue(): mixed;
}