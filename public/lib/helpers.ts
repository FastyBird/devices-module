import { ButtonPayload, DataType, SwitchPayload } from '@fastybird/modules-metadata'
import { parse } from 'date-fns'

export const normalizeValue = (
  dataType: DataType,
  value: string | null,
  enumFormat?: (string | number | null)[] | null,
): number | string | boolean | Date | null => {
  if (value === null) {
    return null
  }

  switch (dataType) {
    case DataType.BOOLEAN:
      return ['true', 't', 'yes', 'y', '1', 'on'].includes(value.toLocaleLowerCase())

    case DataType.FLOAT: {
      const floatValue = parseFloat(value)

      if (Array.isArray(enumFormat) && enumFormat.length === 2) {
        if (enumFormat[0] !== null && enumFormat[0] > floatValue) {
          return null
        }

        if (enumFormat[1] !== null && enumFormat[1] < floatValue) {
          return null
        }
      }

      return floatValue
    }

    case DataType.CHAR:
    case DataType.UCHAR:
    case DataType.SHORT:
    case DataType.USHORT:
    case DataType.INT:
    case DataType.UINT: {
      const intValue = parseInt(value, 10)

      if (Array.isArray(enumFormat) && enumFormat.length === 2) {
        if (enumFormat[0] !== null && enumFormat[0] > intValue) {
          return null
        }

        if (enumFormat[1] !== null && enumFormat[1] < intValue) {
          return null
        }
      }

      return intValue
    }

    case DataType.STRING:
      return value

    case DataType.ENUM:
      if (Array.isArray(enumFormat) && enumFormat.includes(value.toLowerCase())) {
        return value.toLowerCase()
      }

      return null

    case DataType.DATE:
      return parse(value, 'yyyy-MM-DD', new Date())

    case DataType.TIME:
      return parse(value, 'HH:mm:ssxxx', new Date())

    case DataType.DATETIME:
      return parse(value, "yyyy-MM-DD'T'HH:mm:ssxxx", new Date())

    case DataType.COLOR:
      break

    case DataType.BUTTON:
      if (Object.values(ButtonPayload).includes(value.toLowerCase())) {
        return value.toLowerCase()
      }

      return null

    case DataType.SWITCH:
      if (Object.values(SwitchPayload).includes(value.toLowerCase())) {
        return value.toLowerCase()
      }

      return null
  }

  return value
}
