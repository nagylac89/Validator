<?php

declare(strict_types=1);

namespace Nagyl;

class Translation
{
	protected string $lang = "";
	protected array $translations = [];

	public function __construct() {}

	public function setLang(string $lang)
	{
		$path = dirname(__FILE__) . "/langs/" . $lang . ".php";

		if (is_file($path)) {
			$this->lang = $lang;
			$this->translations = include $path;
		}
	}

	public function get(string $key, array $params = []): string
	{

		if (isset($this->translations[$key])) {
			$t = $this->parseParams($this->translations[$key], $params);
			return $t;
		}

		return $key;
	}

	private function parseParams(string $message, array $params): string
	{
		if (count($params) > 0) {
			foreach ($params as $k => $p) {
				if ($p !== null) {
					$val = "";

					if (is_array($p)) {
						$val = join(", ", $p);
					} else {
						$val = $p;
					}
					$message = str_replace(":" . $k, $val, $message);
				}
			}
		}

		return $message;
	}
}
