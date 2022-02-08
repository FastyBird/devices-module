import { ModelPropertiesMapper } from 'jsona'
import {
  IModelPropertiesMapper,
  TJsonaModel,
  TJsonaRelationships,
} from 'jsona/lib/JsonaTypes'

import Device from '@/lib/models/devices/Device'
import Channel from '@/lib/models/channels/Channel'
import Connector from '@/lib/models/connectors/Connector'
import { RelationInterface } from '@/lib/types'

const RELATIONSHIP_NAMES_PROP = 'relationshipNames'

const CONNECTOR_PROPERTY_ENTITY_REG_EXP = '^([a-z0-9.-]+)/property/connector/([a-z0-9-]+)$'
const CONNECTOR_CONTROL_ENTITY_REG_EXP = '^([a-z0-9.-]+)/control/connector$'
const DEVICE_ENTITY_REG_EXP = '^([a-z0-9.-]+)/device/([a-z0-9-]+)$'
const DEVICE_PROPERTY_ENTITY_REG_EXP = '^([a-z0-9.-]+)/property/device/([a-z0-9-]+)$'
const DEVICE_CONTROL_ENTITY_REG_EXP = '^([a-z0-9.-]+)/control/connector$'
const CHANNEL_ENTITY_REG_EXP = '^([a-z0-9.-]+)/channel$'
const CHANNEL_PROPERTY_ENTITY_REG_EXP = '^([a-z0-9.-]+)/property/channel/([a-z0-9-]+)$'
const CHANNEL_CONTROL_ENTITY_REG_EXP = '^([a-z0-9.-]+)/control/connector$'

class JsonApiModelPropertiesMapper extends ModelPropertiesMapper implements IModelPropertiesMapper {
  getAttributes(model: TJsonaModel): { [index: string]: any } {
    const exceptProps = ['id', '$id', 'type', 'draft', RELATIONSHIP_NAMES_PROP]

    const connectorPropertyEntityRegex = new RegExp(CONNECTOR_PROPERTY_ENTITY_REG_EXP)
    const connectorControlEntityRegex = new RegExp(CONNECTOR_CONTROL_ENTITY_REG_EXP)
    const deviceEntityRegex = new RegExp(DEVICE_ENTITY_REG_EXP)
    const devicePropertyEntityRegex = new RegExp(DEVICE_PROPERTY_ENTITY_REG_EXP)
    const deviceControlEntityRegex = new RegExp(DEVICE_CONTROL_ENTITY_REG_EXP)
    const channelEntityRegex = new RegExp(CHANNEL_ENTITY_REG_EXP)
    const channelPropertyEntityRegex = new RegExp(CHANNEL_PROPERTY_ENTITY_REG_EXP)
    const channelControlEntityRegex = new RegExp(CHANNEL_CONTROL_ENTITY_REG_EXP)

    if (
      channelEntityRegex.test(model.type)
      || devicePropertyEntityRegex.test(model.type)
      || deviceControlEntityRegex.test(model.type)
    ) {
      exceptProps.push('deviceId')
      exceptProps.push('device')
      exceptProps.push('deviceBackward')
    } else if (
      channelPropertyEntityRegex.test(model.type)
      || channelControlEntityRegex.test(model.type)
    ) {
      exceptProps.push('channelId')
      exceptProps.push('channel')
      exceptProps.push('channelBackward')
    } else if (
      deviceEntityRegex.test(model.type)
      || connectorPropertyEntityRegex.test(model.type)
      || connectorControlEntityRegex.test(model.type)
    ) {
      exceptProps.push('connectorId')
      exceptProps.push('connector')
      exceptProps.push('connectorBackward')
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
              const snakeSubName = subAttrName.replace(/[A-Z]/g, letter => `_${letter.toLowerCase()}`)

              Object.assign(jsonAttributes, { [snakeSubName]: model[attrName][subAttrName] })
            })
          }

          attributes[snakeName] = jsonAttributes
        }
      })

    return attributes
  }

  getRelationships(model: TJsonaModel): TJsonaRelationships {
    if (
      !Object.prototype.hasOwnProperty.call(model, RELATIONSHIP_NAMES_PROP)
      || !Array.isArray(model[RELATIONSHIP_NAMES_PROP])
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

    if (Object.prototype.hasOwnProperty.call(model, 'connectorId')) {
      const connector = Connector.find(model.connectorId)

      if (connector !== null) {
        relationships.connector = {
          id: connector.id,
          type: connector.type,
        }
      }
    }

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

    return relationships
  }
}

export default JsonApiModelPropertiesMapper
