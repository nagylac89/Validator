<?php

declare(strict_types=1);

namespace Tests\Unit;

use Nagyl\Validator;


test("get_first_level_value_in_array", function () {
	$v = new Validator(["name" => "John"]);

	expect($v->getValue("name"))->toBe("John");
});

test("get_second_level_value_in_array", function () {
	$v = new Validator([
		"name" => "John",
		"role" => [
			"id" => 1,
		]
	]);

	expect($v->getValue("role.id"))->toBe(1);
});

test("get_third_level_value_in_array", function () {
	$v = new Validator([
		"name" => "John",
		"role" => [
			"id" => 1,
			"permissions" => [
				"read" => true,
				"write" => false
			]
		]
	]);

	expect($v->getValue("role.permissions.read"))->toBe(true);
});

test("get_list_all_items_by_name_in_array", function () {
	$v = new Validator([
		"name" => "John",
		"roles" => [
			["id" => 1, "name" => "Admin"],
			["id" => 2, "name" => "User"]
		]
	]);

	expect($v->getValue("roles.*.id"))->toBe([1, 2]);
});

test("get_list_all_items_by_name_in_array_in_array", function () {
	$v = new Validator([
		"name" => "John",
		"roles" => [
			[
				"id" => 1,
				"name" => "Admin",
				"permissions" => [
					["id" => 1, "code" => "p_1"],
					["id" => 2, "code" => "p_2"]
				]
			],
			[
				"id" => 2,
				"name" => "User",
				"permissions" => [
					["id" => 1, "code" => "p_1"],
					["id" => 2, "code" => "p_2"]
				]
			]
		]
	]);

	expect($v->getValue("roles.*.permissions.*.code"))->toBe(["p_1", "p_2", "p_1", "p_2"]);
});
