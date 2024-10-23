<?php
require "./vendor/autoload.php";

use Nagyl\Rules\StringRule;
use Nagyl\Validator;

$values = ["name"	=> null];
$rules =  ["name"	=> "string"];

$v = new Validator($values, $rules);

StringRule::create();
