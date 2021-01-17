<?php declare(strict_types = 1);

use Fig\Http\Message\StatusCodeInterface;

const VALID_TOKEN = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJjb20uZmFzdHliaXJkLmF1dGgtbW9kdWxlIiwianRpIjoiMjQ3MTBlOTYtYTZmYi00ZmM3LWFhMzAtNDcyNzkwNWQzMDRjIiwiaWF0IjoxNTg1NzQyNDAwLCJleHAiOjE1ODU3NDk2MDAsInVzZXIiOiI1ZTc5ZWZiZi1iZDBkLTViN2MtNDZlZi1iZmJkZWZiZmJkMzQiLCJyb2xlcyI6WyJhZG1pbmlzdHJhdG9yIl19.QH_Oo_uzTXAb3pNnHvXYnnX447nfVq2_ggQ9ZxStu4s';
const EXPIRED_TOKEN = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJjb20uZmFzdHliaXJkLmF1dGgtbW9kdWxlIiwianRpIjoiMjM5Nzk0NzAtYmVmNi00ZjE2LTlkNzUtNmFhMWZiYWVjNWRiIiwiaWF0IjoxNTc3ODgwMDAwLCJleHAiOjE1Nzc4ODcyMDAsInVzZXIiOiI1ZTc5ZWZiZi1iZDBkLTViN2MtNDZlZi1iZmJkZWZiZmJkMzQiLCJyb2xlcyI6WyJhZG1pbmlzdHJhdG9yIl19.2k8-_-dsPVQeYnb6OunzDp9fJmiQ2JLQo8GwtjgpBXg';
const INVALID_TOKEN = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJjb20uZmFzdHliaXJkLmF1dGgtbW9kdWxlIiwianRpIjoiODkyNTcxOTQtNWUyMi00NWZjLThhMzEtM2JhNzI5OWM5OTExIiwiaWF0IjoxNTg1NzQyNDAwLCJleHAiOjE1ODU3NDk2MDAsInVzZXIiOiI1ZTc5ZWZiZi1iZDBkLTViN2MtNDZlZi1iZmJkZWZiZmJkMzQiLCJyb2xlcyI6WyJhZG1pbmlzdHJhdG9yIl19.z8hS0hUVtGkiHBeUTdKC_CMqhMIa4uXotPuJJ6Js6S4';
const VALID_TOKEN_USER = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJjb20uZmFzdHliaXJkLmF1dGgtbW9kdWxlIiwianRpIjoiOGIxN2I5ZjMtNWNkMi00OTU0LWJhM2ItNThlZTRiZTUzMjdkIiwiaWF0IjoxNTg1NzQyNDAwLCJleHAiOjE1ODU3NDk2MDAsInVzZXIiOiI1ZTc5ZWZiZi1iZDBkLTViN2MtNDZlZi1iZmJkZWZiZmJkMzQiLCJyb2xlcyI6WyJ1c2VyIl19.jELVcZGRa5_-Jcpoo3Jfho08vQT2IobtoEQPhxN2tzw';

return [
	// Valid responses
	//////////////////
	'create'              => [
		'/v1/devices/bf4cd870-2aac-45f0-a85e-e1cefd2d6d9a/connector',
		'Bearer ' . VALID_TOKEN,
		file_get_contents(__DIR__ . '/requests/device.connector.create.json'),
		StatusCodeInterface::STATUS_CREATED,
		__DIR__ . '/responses/device.connector.create.json',
	],

	// Invalid responses
	////////////////////
	'createUser'          => [
		'/v1/devices/bf4cd870-2aac-45f0-a85e-e1cefd2d6d9a/connector',
		'Bearer ' . VALID_TOKEN_USER,
		file_get_contents(__DIR__ . '/requests/device.connector.create.json'),
		StatusCodeInterface::STATUS_FORBIDDEN,
		__DIR__ . '/responses/generic/forbidden.json',
	],
	'createInvalidDevice' => [
		'/v1/devices/69786d15-fd0c-4d9f-9378-33287c2009fa/connector',
		'Bearer ' . VALID_TOKEN,
		file_get_contents(__DIR__ . '/requests/device.connector.create.json'),
		StatusCodeInterface::STATUS_NOT_FOUND,
		__DIR__ . '/responses/device.connector.create.invalid.relation.json',
	],
	'missingRequired'     => [
		'/v1/devices/bf4cd870-2aac-45f0-a85e-e1cefd2d6d9a/connector',
		'Bearer ' . VALID_TOKEN,
		file_get_contents(__DIR__ . '/requests/device.connector.create.missing.required.json'),
		StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
		__DIR__ . '/responses/device.connector.create.missing.required.json',
	],
	'invalidType'         => [
		'/v1/devices/bf4cd870-2aac-45f0-a85e-e1cefd2d6d9a/connector',
		'Bearer ' . VALID_TOKEN,
		file_get_contents(__DIR__ . '/requests/device.connector.create.invalid.type.json'),
		StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
		__DIR__ . '/responses/generic/invalid.type.json',
	],
	//'notUnique'           => [
	//	'/v1/devices/bf4cd870-2aac-45f0-a85e-e1cefd2d6d9a/connector',
	//	'Bearer ' . VALID_TOKEN,
	//	file_get_contents(__DIR__ . '/requests/device.connector.create.notUnique.json'),
	//	StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY,
	//	__DIR__ . '/responses/generic/identifier.notUnique.json',
	//],
	'missingToken'        => [
		'/v1/devices/bf4cd870-2aac-45f0-a85e-e1cefd2d6d9a/connector',
		null,
		file_get_contents(__DIR__ . '/requests/device.connector.create.json'),
		StatusCodeInterface::STATUS_FORBIDDEN,
		__DIR__ . '/responses/generic/forbidden.json',
	],
	'invalidToken'        => [
		'/v1/devices/bf4cd870-2aac-45f0-a85e-e1cefd2d6d9a/connector',
		'Bearer ' . INVALID_TOKEN,
		file_get_contents(__DIR__ . '/requests/device.connector.create.json'),
		StatusCodeInterface::STATUS_UNAUTHORIZED,
		__DIR__ . '/responses/generic/unauthorized.json',
	],
	'emptyToken'          => [
		'/v1/devices/bf4cd870-2aac-45f0-a85e-e1cefd2d6d9a/connector',
		'',
		file_get_contents(__DIR__ . '/requests/device.connector.create.json'),
		StatusCodeInterface::STATUS_FORBIDDEN,
		__DIR__ . '/responses/generic/forbidden.json',
	],
	'expiredToken'        => [
		'/v1/devices/bf4cd870-2aac-45f0-a85e-e1cefd2d6d9a/connector',
		'Bearer ' . EXPIRED_TOKEN,
		file_get_contents(__DIR__ . '/requests/device.connector.create.json'),
		StatusCodeInterface::STATUS_UNAUTHORIZED,
		__DIR__ . '/responses/generic/unauthorized.json',
	],
	'notAllowed'          => [
		'/v1/devices/bf4cd870-2aac-45f0-a85e-e1cefd2d6d9a/connector',
		'Bearer ' . VALID_TOKEN_USER,
		file_get_contents(__DIR__ . '/requests/device.connector.create.json'),
		StatusCodeInterface::STATUS_FORBIDDEN,
		__DIR__ . '/responses/generic/forbidden.json',
	],
];
