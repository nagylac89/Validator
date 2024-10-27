<?php

declare(strict_types=1);

namespace Tests\Unit;

use Nagyl\Validator;

pest()->beforeEach(function () {
	$database = new \SQLite3('test.sqlite');
	$database->exec("create table users (id int primary key not null, account text not null, name text not null, is_deleted tinyint null)");

	$database->exec("insert into users (id, account, name, is_deleted) values (1, 'account@test.hu', 'test', 0)");
	$database->exec("insert into users (id, account, name, is_deleted) values (2, 'account2@test.hu', 'test 2', 0)");
	$database->exec("insert into users (id, account, name, is_deleted) values (3, 'account3@test.hu', 'tes3', 0)");
	$database->exec("insert into users (id, account, name, is_deleted) values (4, 'account4@test.hu', 'test4', 1)");
	$database->exec("insert into users (id, account, name, is_deleted) values (5, 'account5@test.hu', 'test5', 0)");

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

test('unique_validator_should_be_valid', function () {
	$values = ["account" => "new@account.hu"];

	$v = new Validator($values);
	$v->attr("account")->string()->unique("users", "account")->add();

	expect($v->validate())->toBeTrue();
});

test('unique_validator_should_be_invalid', function () {
	$values = ["account" => "account@test.hu"];

	$v = new Validator($values);
	$v->attr("account")->string()->unique("users", "account")->add();

	expect($v->validate())->toBeFalse();
});

test('unique_validator_should_be_valid_when_ignore_id', function () {
	$values = ["account" => "account@test.hu"];

	$v = new Validator($values);
	$v->attr("account")->string()->unique("users", "account", "and id <> 1")->add();

	expect($v->validate())->toBeTrue();
});


test('unique_validator_should_be_invalid_msg_check', function () {
	$values = ["account" => "account@test.hu"];

	$v = new Validator($values);
	$v->attr("account")->string()->unique("users", "account")->add();
	$v->validate();

	$msg = $v->result()->errors["account"][0];

	expect($msg)->toBe("The account value should be unique!");
});

test('unique_validator_should_be_valid_on_array', function () {
	$values = ["account" => ["account99@test.hu", "account7@test.hu"]];

	$v = new Validator($values);
	$v->attr("account")->array()->string()->unique("users", "account")->add();

	expect($v->validate())->toBeTrue();
});