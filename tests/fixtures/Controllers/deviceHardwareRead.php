<?php declare(strict_types = 1);

use Fig\Http\Message\StatusCodeInterface;

const VALID_TOKEN = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJjb20uZmFzdHliaXJkLmF1dGgtbW9kdWxlIiwianRpIjoiMjQ3MTBlOTYtYTZmYi00ZmM3LWFhMzAtNDcyNzkwNWQzMDRjIiwiaWF0IjoxNTg1NzQyNDAwLCJleHAiOjE1ODU3NDk2MDAsInVzZXIiOiI1ZTc5ZWZiZi1iZDBkLTViN2MtNDZlZi1iZmJkZWZiZmJkMzQiLCJyb2xlcyI6WyJhZG1pbmlzdHJhdG9yIl19.QH_Oo_uzTXAb3pNnHvXYnnX447nfVq2_ggQ9ZxStu4s';
const EXPIRED_TOKEN = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJjb20uZmFzdHliaXJkLmF1dGgtbW9kdWxlIiwianRpIjoiMjM5Nzk0NzAtYmVmNi00ZjE2LTlkNzUtNmFhMWZiYWVjNWRiIiwiaWF0IjoxNTc3ODgwMDAwLCJleHAiOjE1Nzc4ODcyMDAsInVzZXIiOiI1ZTc5ZWZiZi1iZDBkLTViN2MtNDZlZi1iZmJkZWZiZmJkMzQiLCJyb2xlcyI6WyJhZG1pbmlzdHJhdG9yIl19.2k8-_-dsPVQeYnb6OunzDp9fJmiQ2JLQo8GwtjgpBXg';
const INVALID_TOKEN = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJjb20uZmFzdHliaXJkLmF1dGgtbW9kdWxlIiwianRpIjoiODkyNTcxOTQtNWUyMi00NWZjLThhMzEtM2JhNzI5OWM5OTExIiwiaWF0IjoxNTg1NzQyNDAwLCJleHAiOjE1ODU3NDk2MDAsInVzZXIiOiI1ZTc5ZWZiZi1iZDBkLTViN2MtNDZlZi1iZmJkZWZiZmJkMzQiLCJyb2xlcyI6WyJhZG1pbmlzdHJhdG9yIl19.z8hS0hUVtGkiHBeUTdKC_CMqhMIa4uXotPuJJ6Js6S4';
const VALID_TOKEN_USER = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJjb20uZmFzdHliaXJkLmF1dGgtbW9kdWxlIiwianRpIjoiOGIxN2I5ZjMtNWNkMi00OTU0LWJhM2ItNThlZTRiZTUzMjdkIiwiaWF0IjoxNTg1NzQyNDAwLCJleHAiOjE1ODU3NDk2MDAsInVzZXIiOiI1ZTc5ZWZiZi1iZDBkLTViN2MtNDZlZi1iZmJkZWZiZmJkMzQiLCJyb2xlcyI6WyJ1c2VyIl19.jELVcZGRa5_-Jcpoo3Jfho08vQT2IobtoEQPhxN2tzw';

return [
	// Valid responses
	//////////////////
	'read'                           => [
		'/v1/devices/69786d15-fd0c-4d9f-9378-33287c2009fa/hardware',
		'Bearer ' . VALID_TOKEN,
		StatusCodeInterface::STATUS_OK,
		__DIR__ . '/responses/device.hardware.read.json',
	],
	'readUser'                       => [
		'/v1/devices/69786d15-fd0c-4d9f-9378-33287c2009fa/hardware',
		'Bearer ' . VALID_TOKEN_USER,
		StatusCodeInterface::STATUS_OK,
		__DIR__ . '/responses/device.hardware.read.json',
	],
	'readRelationshipsDevice'        => [
		'/v1/devices/69786d15-fd0c-4d9f-9378-33287c2009fa/hardware/relationships/device',
		'Bearer ' . VALID_TOKEN,
		StatusCodeInterface::STATUS_OK,
		__DIR__ . '/responses/device.hardware.relationships.device.json',
	],
	'readRelationshipsDeviceUser'    => [
		'/v1/devices/69786d15-fd0c-4d9f-9378-33287c2009fa/hardware/relationships/device',
		'Bearer ' . VALID_TOKEN_USER,
		StatusCodeInterface::STATUS_OK,
		__DIR__ . '/responses/device.hardware.relationships.device.json',
	],

	// Invalid responses
	////////////////////
	'readRelationshipsUnknown'       => [
		'/v1/devices/69786d15-fd0c-4d9f-9378-33287c2009fa/hardware/relationships/unknown',
		'Bearer ' . VALID_TOKEN,
		StatusCodeInterface::STATUS_NOT_FOUND,
		__DIR__ . '/responses/generic/relation.unknown.json',
	],
	'readRelationshipsUnknownDevice' => [
		'/v1/devices/28bc0d38-2f7c-4a71-aa74-27b102f8dfc4/hardware/relationships/device',
		'Bearer ' . VALID_TOKEN,
		StatusCodeInterface::STATUS_NOT_FOUND,
		__DIR__ . '/responses/generic/notFound.json',
	],
	'readMissingToken'               => [
		'/v1/devices/69786d15-fd0c-4d9f-9378-33287c2009fa/hardware',
		null,
		StatusCodeInterface::STATUS_FORBIDDEN,
		__DIR__ . '/responses/generic/forbidden.json',
	],
	'readEmptyToken'                 => [
		'/v1/devices/69786d15-fd0c-4d9f-9378-33287c2009fa/hardware',
		'',
		StatusCodeInterface::STATUS_FORBIDDEN,
		__DIR__ . '/responses/generic/forbidden.json',
	],
	'readInvalidToken'               => [
		'/v1/devices/69786d15-fd0c-4d9f-9378-33287c2009fa/hardware',
		'Bearer ' . INVALID_TOKEN,
		StatusCodeInterface::STATUS_UNAUTHORIZED,
		__DIR__ . '/responses/generic/unauthorized.json',
	],
	'readExpiredToken'               => [
		'/v1/devices/69786d15-fd0c-4d9f-9378-33287c2009fa/hardware',
		'Bearer ' . EXPIRED_TOKEN,
		StatusCodeInterface::STATUS_UNAUTHORIZED,
		__DIR__ . '/responses/generic/unauthorized.json',
	],
];
