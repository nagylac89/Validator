<?php

declare(strict_types=1);

namespace Tests\Unit;

use Nagyl\Validator;

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

test('exists_validator_should_be_invalid_msg_check', function () {
	$values = ["val" => 4];

	$v = new Validator($values);
	$v->attribute("val")->int()->exists("users", "id", "and is_deleted = 0")->add();
	$v->validate();

	$msg = $v->result()->errors["val"][0];

	expect($msg)->toBe("Invalid value: val!");
});

test("array_validator_should_be_valid_on_exists_values", function () {
	$values = ["val" => [1, 2, 3, 3]];

	$v = new Validator($values);
	$v->attribute("val")->array()->int()->exists("users", "id")->add();

	expect($v->validate())->toBeTrue();
});

test("array_validator_should_be_valid_on_exists_values_additional_query", function () {
	$values = ["val" => [1, 2, 3, 4]];

	$v = new Validator($values);
	$v->attribute("val")->array()->int()->exists("users", "id", "and is_deleted = 0")->add();

	expect($v->validate())->toBeFalse();
});

test("exists_validator_test_on_array_object", function () {
	$v = new Validator([
		"users" => [
			["id" => 1],
			["id" => 2],
			["id" => 3],
			["id" => 4],
			["id" => 5]
		]
	]);

	$v->attribute("users.*.id")->int()->exists("users", "id")->add();
	expect($v->validate())->toBeTrue();
});