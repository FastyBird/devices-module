import { ModelPropertiesMapper } from 'jsona'
import {
  IModelPropertiesMapper,
  TJsonaModel,
  TJsonaRelationships,
} from 'jsona/lib/JsonaTypes'

import Device from '@/lib/models/devices/Device'
import { DevicePropertyEntityTypes } from '@/lib/models/device-properties/types'
import { DeviceConfigurationEntityTypes } from '@/lib/models/device-configuration/types'
import Channel from '@/lib/models/channels/Channel'
import { ChannelEntityTypes } from '@/lib/models/channels/types'
import { ChannelPropertyEntityTypes } from '@/lib/models/channel-properties/types'
import { ChannelConfigurationEntityTypes } from '@/lib/models/channel-configuration/types'
import { DeviceConnectorEntityTypes } from '@/lib/models/device-connector/types'
import Connector from '@/lib/models/connectors/Connector'
import { RelationInterface } from '@/lib/types'

const RELATIONSHIP_NAMES_PROP = 'relationshipNames'

class JsonApiModelPropertiesMapper extends ModelPropertiesMapper implements IModelPropertiesMapper {
  getAttributes(model: TJsonaModel): { [index: string]: any } {
    const exceptProps = ['id', '$id', 'type', 'draft', RELATIONSHIP_NAMES_PROP]

    if (
      model.type === ChannelEntityTypes.CHANNEL ||
      model.type === DevicePropertyEntityTypes.PROPERTY ||
      model.type === DeviceConfigurationEntityTypes.CONFIGURATION ||
      model.type === DeviceConnectorEntityTypes.CONNECTOR
    ) {
      exceptProps.push('deviceId')
      exceptProps.push('device')
      exceptProps.push('device_backward')

      if (model.type === DeviceConnectorEntityTypes.CONNECTOR) {
        exceptProps.push('connectorId')
        exceptProps.push('connector')
        exceptProps.push('connector_backward')
      }
    } else if (
      model.type === ChannelPropertyEntityTypes.PROPERTY ||
      model.type === ChannelConfigurationEntityTypes.CONFIGURATION
    ) {
      exceptProps.push('channelId')
      exceptProps.push('channel')
      exceptProps.push('channel_backward')
    }

    if (Array.isArray(model[RELATIONSHIP_NAMES_PROP])) {
      exceptProps.push(...model[RELATIONSHIP_NAMES_PROP])
    }

    const attributes: { [index: string]: any } = {}

    Object.keys(model)
      .forEach((attrName) => {
        if (!exceptProps.includes(attrName)) {
          const snakeName = attrName.replace(/[A-Z]/g, letter => `_${letter.toLowerCase()}`)

          let jsonAttributes = model[attrName]

          if (typeof jsonAttributes === 'object' && jsonAttributes !== null) {
            jsonAttributes = {}

            Object.keys(model[attrName]).forEach((subAttrName) => {
              const kebabSubName = subAttrName.replace(/([a-z][A-Z0-9])/g, g => `${g[0]}_${g[1].toLowerCase()}`)

              Object.assign(jsonAttributes, { [kebabSubName]: model[attrName][subAttrName] })
            })
          }

          attributes[snakeName] = jsonAttributes
        }
      })

    return attributes
  }

  getRelationships(model: TJsonaModel): TJsonaRelationships {
    if (
      !Object.prototype.hasOwnProperty.call(model, RELATIONSHIP_NAMES_PROP) ||
      !Array.isArray(model[RELATIONSHIP_NAMES_PROP])
    ) {
      return {}
    }

    const relationshipNames = model[RELATIONSHIP_NAMES_PROP]

    const relationships: { [index: string]: RelationInterface | RelationInterface[] } = {}

    relationshipNames
      .forEach((relationName: string) => {
        const snakeName = relationName.replace(/[A-Z]/g, letter => `_${letter.toLowerCase()}`)

        if (model[relationName] !== undefined) {
          if (Array.isArray(model[relationName])) {
            relationships[snakeName] = model[relationName]
              .map((item: TJsonaModel) => {
                return {
                  id: item.id,
                  type: item.type,
                }
              })
          } else if (typeof model[relationName] === 'object' && model[relationName] !== null) {
            relationships[snakeName] = {
              id: model[relationName].id,
              type: model[relationName].type,
            }
          }
        }
      })

    if (Object.prototype.hasOwnProperty.call(model, 'deviceId')) {
      const device = Device.find(model.deviceId)

      if (device !== null) {
        relationships.device = {
          id: device.id,
          type: device.type,
        }
      }
    }

    if (Object.prototype.hasOwnProperty.call(model, 'channelId')) {
      const channel = Channel.find(model.deviceId)

      if (channel !== null) {
        relationships.channel = {
          id: channel.id,
          type: channel.type,
        }
      }
    }

    if (Object.prototype.hasOwnProperty.call(model, 'connectorId')) {
      const connector = Connector.find(model.connectorId)

      if (connector !== null) {
        relationships.connector = {
          id: connector.id,
          type: connector.type,
        }
      }
    }

    return relationships
  }
}

export default JsonApiModelPropertiesMapper
