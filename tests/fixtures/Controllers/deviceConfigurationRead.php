<?php declare(strict_types = 1);

use Fig\Http\Message\StatusCodeInterface;

const VALID_TOKEN = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJjb20uZmFzdHliaXJkLmF1dGgtbW9kdWxlIiwianRpIjoiMjQ3MTBlOTYtYTZmYi00ZmM3LWFhMzAtNDcyNzkwNWQzMDRjIiwiaWF0IjoxNTg1NzQyNDAwLCJleHAiOjE1ODU3NDk2MDAsInVzZXIiOiI1ZTc5ZWZiZi1iZDBkLTViN2MtNDZlZi1iZmJkZWZiZmJkMzQiLCJyb2xlcyI6WyJhZG1pbmlzdHJhdG9yIl19.QH_Oo_uzTXAb3pNnHvXYnnX447nfVq2_ggQ9ZxStu4s';
const EXPIRED_TOKEN = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJjb20uZmFzdHliaXJkLmF1dGgtbW9kdWxlIiwianRpIjoiMjM5Nzk0NzAtYmVmNi00ZjE2LTlkNzUtNmFhMWZiYWVjNWRiIiwiaWF0IjoxNTc3ODgwMDAwLCJleHAiOjE1Nzc4ODcyMDAsInVzZXIiOiI1ZTc5ZWZiZi1iZDBkLTViN2MtNDZlZi1iZmJkZWZiZmJkMzQiLCJyb2xlcyI6WyJhZG1pbmlzdHJhdG9yIl19.2k8-_-dsPVQeYnb6OunzDp9fJmiQ2JLQo8GwtjgpBXg';
const INVALID_TOKEN = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJjb20uZmFzdHliaXJkLmF1dGgtbW9kdWxlIiwianRpIjoiODkyNTcxOTQtNWUyMi00NWZjLThhMzEtM2JhNzI5OWM5OTExIiwiaWF0IjoxNTg1NzQyNDAwLCJleHAiOjE1ODU3NDk2MDAsInVzZXIiOiI1ZTc5ZWZiZi1iZDBkLTViN2MtNDZlZi1iZmJkZWZiZmJkMzQiLCJyb2xlcyI6WyJhZG1pbmlzdHJhdG9yIl19.z8hS0hUVtGkiHBeUTdKC_CMqhMIa4uXotPuJJ6Js6S4';
const VALID_TOKEN_USER = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJjb20uZmFzdHliaXJkLmF1dGgtbW9kdWxlIiwianRpIjoiOGIxN2I5ZjMtNWNkMi00OTU0LWJhM2ItNThlZTRiZTUzMjdkIiwiaWF0IjoxNTg1NzQyNDAwLCJleHAiOjE1ODU3NDk2MDAsInVzZXIiOiI1ZTc5ZWZiZi1iZDBkLTViN2MtNDZlZi1iZmJkZWZiZmJkMzQiLCJyb2xlcyI6WyJ1c2VyIl19.jELVcZGRa5_-Jcpoo3Jfho08vQT2IobtoEQPhxN2tzw';

return [
	// Valid responses
	//////////////////
	'readAll'                                     => [
		'/v1/devices/69786d15-fd0c-4d9f-9378-33287c2009fa/configuration',
		'Bearer ' . VALID_TOKEN,
		StatusCodeInterface::STATUS_OK,
		__DIR__ . '/responses/device.configuration.index.json',
	],
	'readAllPaging'                               => [
		'/v1/devices/69786d15-fd0c-4d9f-9378-33287c2009fa/configuration?page[offset]=1&page[limit]=1',
		'Bearer ' . VALID_TOKEN,
		StatusCodeInterface::STATUS_OK,
		__DIR__ . '/responses/device.configuration.index.paging.json',
	],
	'readOne'                                     => [
		'/v1/devices/69786d15-fd0c-4d9f-9378-33287c2009fa/configuration/138c6cfc-ed49-476b-9f1e-6ee1dcb24f0b',
		'Bearer ' . VALID_TOKEN,
		StatusCodeInterface::STATUS_OK,
		__DIR__ . '/responses/device.configuration.read.json',
	],
	'readRelationshipsDevice'                     => [
		'/v1/devices/69786d15-fd0c-4d9f-9378-33287c2009fa/configuration/138c6cfc-ed49-476b-9f1e-6ee1dcb24f0b/relationships/device',
		'Bearer ' . VALID_TOKEN,
		StatusCodeInterface::STATUS_OK,
		__DIR__ . '/responses/device.configuration.relationships.device.json',
	],

	// Invalid responses
	////////////////////
	'readOneUnknown'                              => [
		'/v1/devices/69786d15-fd0c-4d9f-9378-33287c2009fa/configuration/28bc0d38-2f7c-4a71-aa74-27b102f8dfc4',
		'Bearer ' . VALID_TOKEN,
		StatusCodeInterface::STATUS_NOT_FOUND,
		__DIR__ . '/responses/generic/notFound.json',
	],
	'readRelationshipsDeviceUnknownConfiguration' => [
		'/v1/devices/69786d15-fd0c-4d9f-9378-33287c2009fa/configuration/28bc0d38-2f7c-4a71-aa74-27b102f8dfc4/relationships/device',
		'Bearer ' . VALID_TOKEN,
		StatusCodeInterface::STATUS_NOT_FOUND,
		__DIR__ . '/responses/generic/notFound.json',
	],
	'readRelationshipsUnknown'                    => [
		'/v1/devices/69786d15-fd0c-4d9f-9378-33287c2009fa/configuration/138c6cfc-ed49-476b-9f1e-6ee1dcb24f0b/relationships/unknown',
		'Bearer ' . VALID_TOKEN,
		StatusCodeInterface::STATUS_NOT_FOUND,
		__DIR__ . '/responses/generic/relation.unknown.json',
	],
	'readRelationshipsUnknownDevice'              => [
		'/v1/devices/28bc0d38-2f7c-4a71-aa74-27b102f8dfc4/configuration/138c6cfc-ed49-476b-9f1e-6ee1dcb24f0b/relationships/device',
		'Bearer ' . VALID_TOKEN,
		StatusCodeInterface::STATUS_NOT_FOUND,
		__DIR__ . '/responses/generic/notFound.json',
	],
	'readAllMissingToken'                         => [
		'/v1/devices/69786d15-fd0c-4d9f-9378-33287c2009fa/configuration',
		null,
		StatusCodeInterface::STATUS_FORBIDDEN,
		__DIR__ . '/responses/generic/forbidden.json',
	],
	'readOneMissingToken'                         => [
		'/v1/devices/69786d15-fd0c-4d9f-9378-33287c2009fa/configuration/138c6cfc-ed49-476b-9f1e-6ee1dcb24f0b',
		null,
		StatusCodeInterface::STATUS_FORBIDDEN,
		__DIR__ . '/responses/generic/forbidden.json',
	],
	'readAllEmptyToken'                           => [
		'/v1/devices/69786d15-fd0c-4d9f-9378-33287c2009fa/configuration',
		'',
		StatusCodeInterface::STATUS_FORBIDDEN,
		__DIR__ . '/responses/generic/forbidden.json',
	],
	'readOneEmptyToken'                           => [
		'/v1/devices/69786d15-fd0c-4d9f-9378-33287c2009fa/configuration/138c6cfc-ed49-476b-9f1e-6ee1dcb24f0b',
		'',
		StatusCodeInterface::STATUS_FORBIDDEN,
		__DIR__ . '/responses/generic/forbidden.json',
	],
	'readAllInvalidToken'                         => [
		'/v1/devices/69786d15-fd0c-4d9f-9378-33287c2009fa/configuration',
		'Bearer ' . INVALID_TOKEN,
		StatusCodeInterface::STATUS_UNAUTHORIZED,
		__DIR__ . '/responses/generic/unauthorized.json',
	],
	'readOneInvalidToken'                         => [
		'/v1/devices/69786d15-fd0c-4d9f-9378-33287c2009fa/configuration/138c6cfc-ed49-476b-9f1e-6ee1dcb24f0b',
		'Bearer ' . INVALID_TOKEN,
		StatusCodeInterface::STATUS_UNAUTHORIZED,
		__DIR__ . '/responses/generic/unauthorized.json',
	],
	'readAllExpiredToken'                         => [
		'/v1/devices/69786d15-fd0c-4d9f-9378-33287c2009fa/configuration',
		'Bearer ' . EXPIRED_TOKEN,
		StatusCodeInterface::STATUS_UNAUTHORIZED,
		__DIR__ . '/responses/generic/unauthorized.json',
	],
	'readOneExpiredToken'                         => [
		'/v1/devices/69786d15-fd0c-4d9f-9378-33287c2009fa/configuration/138c6cfc-ed49-476b-9f1e-6ee1dcb24f0b',
		'Bearer ' . EXPIRED_TOKEN,
		StatusCodeInterface::STATUS_UNAUTHORIZED,
		__DIR__ . '/responses/generic/unauthorized.json',
	],
];
