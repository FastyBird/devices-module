import { DataType } from '@fastybird/metadata'

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