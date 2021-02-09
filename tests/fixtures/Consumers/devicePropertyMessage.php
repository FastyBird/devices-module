<?php declare(strict_types = 1);

use FastyBird\ModulesMetadata;
use Nette\Utils;

return [
	'messageWithUpdate'    => [
		ModulesMetadata\Constants::MESSAGE_BUS_DEVICES_PROPERTIES_DATA_ROUTING_KEY,
		ModulesMetadata\Constants::MODULE_UI_ORIGIN,
		Utils\ArrayHash::from([
			'device'   => 'bLikkz',
			'property' => 'bLikvt',
			'expected' => 'off',
		]),
		[
			'value'    => 'on',
			'expected' => 'off',
			'pending'  => true,
		],
	],
	'messageWithoutUpdate' => [
		ModulesMetadata\Constants::MESSAGE_BUS_DEVICES_PROPERTIES_DATA_ROUTING_KEY,
		ModulesMetadata\Constants::MODULE_UI_ORIGIN,
		Utils\ArrayHash::from([
			'device'   => 'bLikkz',
			'property' => 'bLikvt',
			'expected' => 'on',
		]),
		[
			'value'    => 'on',
			'expected' => null,
			'pending'  => false,
		],
	],
];
