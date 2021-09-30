<?php declare(strict_types = 1);

use Fig\Http\Message\StatusCodeInterface;

const VALID_TOKEN = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJjb20uZmFzdHliaXJkLmF1dGgtbW9kdWxlIiwianRpIjoiMjQ3MTBlOTYtYTZmYi00ZmM3LWFhMzAtNDcyNzkwNWQzMDRjIiwiaWF0IjoxNTg1NzQyNDAwLCJleHAiOjE1ODU3NDk2MDAsInVzZXIiOiI1ZTc5ZWZiZi1iZDBkLTViN2MtNDZlZi1iZmJkZWZiZmJkMzQiLCJyb2xlcyI6WyJhZG1pbmlzdHJhdG9yIl19.QH_Oo_uzTXAb3pNnHvXYnnX447nfVq2_ggQ9ZxStu4s';
const EXPIRED_TOKEN = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJjb20uZmFzdHliaXJkLmF1dGgtbW9kdWxlIiwianRpIjoiMjM5Nzk0NzAtYmVmNi00ZjE2LTlkNzUtNmFhMWZiYWVjNWRiIiwiaWF0IjoxNTc3ODgwMDAwLCJleHAiOjE1Nzc4ODcyMDAsInVzZXIiOiI1ZTc5ZWZiZi1iZDBkLTViN2MtNDZlZi1iZmJkZWZiZmJkMzQiLCJyb2xlcyI6WyJhZG1pbmlzdHJhdG9yIl19.2k8-_-dsPVQeYnb6OunzDp9fJmiQ2JLQo8GwtjgpBXg';
const INVALID_TOKEN = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJjb20uZmFzdHliaXJkLmF1dGgtbW9kdWxlIiwianRpIjoiODkyNTcxOTQtNWUyMi00NWZjLThhMzEtM2JhNzI5OWM5OTExIiwiaWF0IjoxNTg1NzQyNDAwLCJleHAiOjE1ODU3NDk2MDAsInVzZXIiOiI1ZTc5ZWZiZi1iZDBkLTViN2MtNDZlZi1iZmJkZWZiZmJkMzQiLCJyb2xlcyI6WyJhZG1pbmlzdHJhdG9yIl19.z8hS0hUVtGkiHBeUTdKC_CMqhMIa4uXotPuJJ6Js6S4';
const VALID_TOKEN_USER = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJjb20uZmFzdHliaXJkLmF1dGgtbW9kdWxlIiwianRpIjoiOGIxN2I5ZjMtNWNkMi00OTU0LWJhM2ItNThlZTRiZTUzMjdkIiwiaWF0IjoxNTg1NzQyNDAwLCJleHAiOjE1ODU3NDk2MDAsInVzZXIiOiI1ZTc5ZWZiZi1iZDBkLTViN2MtNDZlZi1iZmJkZWZiZmJkMzQiLCJyb2xlcyI6WyJ1c2VyIl19.jELVcZGRa5_-Jcpoo3Jfho08vQT2IobtoEQPhxN2tzw';

return [
	// Valid responses
	//////////////////
	'readAll'                                => [
		'/v1/connectors/17c59dfa-2edd-438e-8c49-faa4e38e5a5e/controls',
		'Bearer ' . VALID_TOKEN,
		StatusCodeInterface::STATUS_OK,
		__DIR__ . '/responses/connector.controls.index.json',
	],
	'readAllPaging'                          => [
		'/v1/connectors/17c59dfa-2edd-438e-8c49-faa4e38e5a5e/controls?page[offset]=1&page[limit]=1',
		'Bearer ' . VALID_TOKEN,
		StatusCodeInterface::STATUS_OK,
		__DIR__ . '/responses/connector.controls.index.paging.json',
	],
	'readOne'                                => [
		'/v1/connectors/17c59dfa-2edd-438e-8c49-faa4e38e5a5e/controls/7c055b2b-60c3-4017-93db-e9478d8aa662',
		'Bearer ' . VALID_TOKEN,
		StatusCodeInterface::STATUS_OK,
		__DIR__ . '/responses/connector.controls.read.json',
	],
	'readRelationshipsConnector'               => [
		'/v1/connectors/17c59dfa-2edd-438e-8c49-faa4e38e5a5e/controls/7c055b2b-60c3-4017-93db-e9478d8aa662/relationships/connector',
		'Bearer ' . VALID_TOKEN,
		StatusCodeInterface::STATUS_OK,
		__DIR__ . '/responses/connector.controls.relationships.connector.json',
	],

	// Invalid responses
	////////////////////
	'readOneUnknown'                         => [
		'/v1/connectors/17c59dfa-2edd-438e-8c49-faa4e38e5a5e/controls/28bc0d38-2f7c-4a71-aa74-27b102f8dfc4',
		'Bearer ' . VALID_TOKEN,
		StatusCodeInterface::STATUS_NOT_FOUND,
		__DIR__ . '/responses/generic/notFound.json',
	],
	'readRelationshipsConnectorUnknownControl' => [
		'/v1/connectors/17c59dfa-2edd-438e-8c49-faa4e38e5a5e/controls/28bc0d38-2f7c-4a71-aa74-27b102f8dfc4/relationships/connector',
		'Bearer ' . VALID_TOKEN,
		StatusCodeInterface::STATUS_NOT_FOUND,
		__DIR__ . '/responses/generic/notFound.json',
	],
	'readRelationshipsUnknownConnector'         => [
		'/v1/connectors/69786d15-fd0c-4d9f-9378-33287c2009af/controls/7c055b2b-60c3-4017-93db-e9478d8aa662/relationships/connector',
		'Bearer ' . VALID_TOKEN,
		StatusCodeInterface::STATUS_NOT_FOUND,
		__DIR__ . '/responses/generic/notFound.json',
	],
	'readRelationshipsUnknown'               => [
		'/v1/connectors/17c59dfa-2edd-438e-8c49-faa4e38e5a5e/controls/7c055b2b-60c3-4017-93db-e9478d8aa662/relationships/unknown',
		'Bearer ' . VALID_TOKEN,
		StatusCodeInterface::STATUS_NOT_FOUND,
		__DIR__ . '/responses/generic/relation.unknown.json',
	],
	'readAllMissingToken'                    => [
		'/v1/connectors/17c59dfa-2edd-438e-8c49-faa4e38e5a5e/controls',
		null,
		StatusCodeInterface::STATUS_FORBIDDEN,
		__DIR__ . '/responses/generic/forbidden.json',
	],
	'readOneMissingToken'                    => [
		'/v1/connectors/17c59dfa-2edd-438e-8c49-faa4e38e5a5e/controls/7c055b2b-60c3-4017-93db-e9478d8aa662',
		null,
		StatusCodeInterface::STATUS_FORBIDDEN,
		__DIR__ . '/responses/generic/forbidden.json',
	],
	'readAllEmptyToken'                      => [
		'/v1/connectors/17c59dfa-2edd-438e-8c49-faa4e38e5a5e/controls',
		'',
		StatusCodeInterface::STATUS_FORBIDDEN,
		__DIR__ . '/responses/generic/forbidden.json',
	],
	'readOneEmptyToken'                      => [
		'/v1/connectors/17c59dfa-2edd-438e-8c49-faa4e38e5a5e/controls/7c055b2b-60c3-4017-93db-e9478d8aa662',
		'',
		StatusCodeInterface::STATUS_FORBIDDEN,
		__DIR__ . '/responses/generic/forbidden.json',
	],
	'readAllInvalidToken'                    => [
		'/v1/connectors/17c59dfa-2edd-438e-8c49-faa4e38e5a5e/controls',
		'Bearer ' . INVALID_TOKEN,
		StatusCodeInterface::STATUS_UNAUTHORIZED,
		__DIR__ . '/responses/generic/unauthorized.json',
	],
	'readOneInvalidToken'                    => [
		'/v1/connectors/17c59dfa-2edd-438e-8c49-faa4e38e5a5e/controls/7c055b2b-60c3-4017-93db-e9478d8aa662',
		'Bearer ' . INVALID_TOKEN,
		StatusCodeInterface::STATUS_UNAUTHORIZED,
		__DIR__ . '/responses/generic/unauthorized.json',
	],
	'readAllExpiredToken'                    => [
		'/v1/connectors/17c59dfa-2edd-438e-8c49-faa4e38e5a5e/controls',
		'Bearer ' . EXPIRED_TOKEN,
		StatusCodeInterface::STATUS_UNAUTHORIZED,
		__DIR__ . '/responses/generic/unauthorized.json',
	],
	'readOneExpiredToken'                    => [
		'/v1/connectors/17c59dfa-2edd-438e-8c49-faa4e38e5a5e/controls/7c055b2b-60c3-4017-93db-e9478d8aa662',
		'Bearer ' . EXPIRED_TOKEN,
		StatusCodeInterface::STATUS_UNAUTHORIZED,
		__DIR__ . '/responses/generic/unauthorized.json',
	],
];