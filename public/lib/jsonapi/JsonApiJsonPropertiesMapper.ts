import { JsonPropertiesMapper } from 'jsona'
import {
  IJsonPropertiesMapper,
  TAnyKeyValueObject,
  TJsonaModel,
  TJsonaRelationships,
  TJsonaRelationshipBuild,
} from 'jsona/lib/JsonaTypes'
import { defineRelationGetter } from 'jsona/lib/simplePropertyMappers'
import clone from 'lodash/clone'
import get from 'lodash/get'

const RELATIONSHIP_NAMES_PROP = 'relationshipNames'
const CASE_REG_EXP = '_([a-z0-9])'
const CONNECTOR_ENTITY_REG_EXP = '^([a-z0-9.-]+)/connector/([a-z0-9.]+)$'
const DEVICE_ENTITY_REG_EXP = '^([a-z0-9.-]+)/device/([a-z0-9.]+)$'
const CHANNEL_ENTITY_REG_EXP = '^([a-z0-9.-]+)/channel$'

class JsonApiJsonPropertiesMapper extends JsonPropertiesMapper implements IJsonPropertiesMapper {

  createModel(type: string): TJsonaModel {
    return { type }
  }

  setId(model: TJsonaModel, id: string): void {
    Object.assign(model, { id })
  }

  setAttributes(model: TJsonaModel, attributes: TAnyKeyValueObject): void {
    const regex = new RegExp(CASE_REG_EXP, 'g')

    Object.keys(attributes).forEach((propName) => {
      const camelName = propName.replace(regex, g => g[1].toUpperCase())

      let modelAttributes = attributes[propName]

      if (typeof modelAttributes === 'object' && modelAttributes !== null) {
        modelAttributes = {}

        Object.keys(attributes[propName]).forEach((subPropName) => {
          const camelSubName = subPropName.replace(regex, g => g[1].toUpperCase())

          Object.assign(modelAttributes, { [camelSubName]: attributes[propName][subPropName] })
        })
      }

      if (propName === 'control') {
        modelAttributes = Object.values(attributes[propName])
      }

      Object.assign(model, { [camelName]: modelAttributes })
    })

    // Entity received via api is not a draft entity
    Object.assign(model, { draft: false })
  }

  setRelationships(model: TJsonaModel, relationships: TJsonaRelationships): void {
    Object.keys(relationships)
      .forEach((propName) => {
        const case_regex = new RegExp(CASE_REG_EXP, 'g')
        const connector_entity_regex = new RegExp(CONNECTOR_ENTITY_REG_EXP)
        const device_entity_regex = new RegExp(DEVICE_ENTITY_REG_EXP)
        const channel_entity_regex = new RegExp(CHANNEL_ENTITY_REG_EXP)

        const camelName = propName.replace(case_regex, g => g[1].toUpperCase())

        if (typeof relationships[propName] === 'function') {
          defineRelationGetter(model, propName, relationships[propName] as TJsonaRelationshipBuild)
        } else {
          const relation = clone(relationships[propName])

          if (Array.isArray(relation)) {
            Object.assign(
              model,
              {
                [camelName]: relation.map((item: TJsonaModel) => {
                  let transformed = item

                  transformed = this.transformDevice(transformed)
                  transformed = this.transformChannel(transformed)

                  return transformed
                }),
              },
            )
          } else if (connector_entity_regex.test(String(get(relation, 'type')).toLowerCase())) {
            Object.assign(model, { connectorId: get(relation, 'id') })
          } else if (device_entity_regex.test(String(get(relation, 'type')).toLowerCase())) {
            Object.assign(model, { deviceId: get(relation, 'id') })
          } else if (channel_entity_regex.test(String(get(relation, 'type')).toLowerCase())) {
            Object.assign(model, { channelId: get(relation, 'id') })
          } else {
            Object.assign(model, { [camelName]: relation })
          }
        }
      })

    const newNames = Object.keys(relationships)
    const currentNames = model[RELATIONSHIP_NAMES_PROP]

    if (currentNames && currentNames.length) {
      Object.assign(model, { [RELATIONSHIP_NAMES_PROP]: [...currentNames, ...newNames].filter((value, i, self) => self.indexOf(value) === i) })
    } else {
      Object.assign(model, { [RELATIONSHIP_NAMES_PROP]: newNames })
    }
  }

  transformDevice(item: TJsonaModel): TJsonaModel {
    if (Object.prototype.hasOwnProperty.call(item, 'device')) {
      Object.assign(item, { deviceId: item.device.id })
      Reflect.deleteProperty(item, 'device')
    }

    return item
  }

  transformChannel(item: TJsonaModel): TJsonaModel {
    if (Object.prototype.hasOwnProperty.call(item, 'channel')) {
      Object.assign(item, { channelId: item.channel.id })
      Reflect.deleteProperty(item, 'channel')
    }

    return item
  }
}

export default JsonApiJsonPropertiesMapper
