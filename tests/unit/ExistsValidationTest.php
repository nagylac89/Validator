<?php

declare(strict_types=1);

namespace Tests\Unit;

use Nagyl\Validator;

$database = null;

pest()->beforeEach(function () {
	$database = new \SQLite3('test.sqlite');
	$database->exec("create table users (id int primary key not null, name text not null, is_deleted tinyint null)");

	$database->exec("insert into users (id, name, is_deleted) values (1, 'test', 0)");
	$database->exec("insert into users (id, name, is_deleted) values (2, 'test 2', 0)");
	$database->exec("insert into users (id, name, is_deleted) values (3, 'tes3', 0)");
	$database->exec("insert into users (id, name, is_deleted) values (4, 'test4', 1)");
	$database->exec("insert into users (id, name, is_deleted) values (5, 'test5', 0)");

	Validator::setQueryFetcher(function (string $qs) use ($database) {
		$retval = [];

		try {
			$r = $database->query($qs);
			while ($result = $r->fetchArray()) {
				$retval[] = $result;
			}
		} catch (\Exception $ex) {

		}

		return $retval;
	});
})->afterEach(function () {
	$database = new \SQLite3('test.sqlite');
	$database->exec("drop table users");
});

test('exists_validator_should_be_valid', function () {
	$values = ["val" => 1];


	$v = new Validator($values);
	$v->attribute("val")->int()->exists("users", "id")->add();


	expect($v->validate())->toBeTrue();
});

test('exists_validator_should_be_invalid', function () {
	$values = ["val" => 999];


	$v = new Validator($values);
	$v->attribute("val")->int()->exists("users", "id")->add();


	expect($v->validate())->toBeFalse();
});

test('exists_validator_should_be_invalid_onadditional', function () {
	$values = ["val" => 4];


	$v = new Validator($values);
	$v->attribute("val")->int()->exists("users", "id", "and is_deleted = 0")->add();


	expect($v->validate())->toBeFalse();
});

test('exists_validator_should_be_valid_onadditional', function () {
	$values = ["val" => 1];

	$v = new Validator($values);
	$v->attribute("val")->int()->exists("users", "id", "and is_deleted = 0")->add();


	expect($v->validate())->toBeTrue();
});