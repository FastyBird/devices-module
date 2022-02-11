import { DataType } from '@fastybird/metadata'

export const CONNECTOR_ENTITY_REG_EXP = '^(?<source>[a-z0-9.-]+)/connector/(?<type>[a-z0-9.]+)$'
export const CONNECTOR_PROPERTY_ENTITY_REG_EXP = '^(?<source>[a-z0-9.-]+)/property/connector/(?<type>[a-z0-9-]+)$'
export const CONNECTOR_CONTROL_ENTITY_REG_EXP = '^(?<source>[a-z0-9.-]+)/control/connector$'

export const DEVICE_ENTITY_REG_EXP = '^(?<source>[a-z0-9.-]+)/device/(?<type>[a-z0-9-]+)$'
export const DEVICE_PROPERTY_ENTITY_REG_EXP = '^(?<source>[a-z0-9.-]+)/property/device/(?<type>[a-z0-9-]+)$'
export const DEVICE_CONTROL_ENTITY_REG_EXP = '^(?<source>[a-z0-9.-]+)/control/connector$'

export const CHANNEL_ENTITY_REG_EXP = '^(?<source>[a-z0-9.-]+)/channel$'
export const CHANNEL_PROPERTY_ENTITY_REG_EXP = '^(?<source>[a-z0-9.-]+)/property/channel/(?<type>[a-z0-9-]+)$'
export const CHANNEL_CONTROL_ENTITY_REG_EXP = '^(?<source>[a-z0-9.-]+)/control/connector$'

export const ANY_PROPERTY_ENTITY_REG_EXP = '^(?<source>[a-z0-9.-]+)/property/(?<parent>[a-z0-9-]+)/(?<type>[a-z0-9-]+)$'

export const cleanInvalid = (
  dataType: string | null,
  rawInvalid: string | number | null,
): string | number | null => {
  if (rawInvalid === null) {
    return null
  }

  if (dataType !== null) {
    switch (dataType) {
      case DataType.CHAR:
      case DataType.UCHAR:
      case DataType.SHORT:
      case DataType.USHORT:
      case DataType.INT:
      case DataType.UINT: {
        if (!isNaN(Number(rawInvalid))) {
          return parseInt(String(rawInvalid), 10)
        }

        break
      }

      case DataType.FLOAT: {
        if (!isNaN(Number(rawInvalid))) {
          return parseFloat(String(rawInvalid))
        }

        break
      }

      default: {
        return String(rawInvalid)
      }
    }
  }

  return null
}