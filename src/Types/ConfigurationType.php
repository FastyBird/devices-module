<?php declare(strict_types = 1);

/**
 * ChannelCategory.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Types
 * @since          1.0.0
 *
 * @date           09.04.23
 */

namespace FastyBird\Module\Devices\Types;

/**
 * Configuration cache types
 *
 * @package        FastyBird:DevicesModule!
 * @subpackage     Types
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
enum ConfigurationType: string
{

	case CONNECTORS = 'connectors';

	case CONNECTORS_PROPERTIES = 'connectors_properties';

	case CONNECTORS_CONTROLS = 'connectors_controls';

	case DEVICES = 'devices';

	case DEVICES_PROPERTIES = 'devices_properties';

	case DEVICES_CONTROLS = 'devices_controls';

	case CHANNELS = 'channels';

	case CHANNELS_PROPERTIES = 'channels_properties';

	case CHANNELS_CONTROLS = 'channels_controls';

}
