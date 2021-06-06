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

import { DeviceEntityTypes } from '@/lib/models/devices/types'
import { ChannelEntityTypes } from '@/lib/models/channels/types'

const RELATIONSHIP_NAMES_PROP = 'relationshipNames'

class JsonApiJsonPropertiesMapper extends JsonPropertiesMapper implements IJsonPropertiesMapper {
  private caseRegExp = '_([a-z0-9])'

  createModel(type: string): TJsonaModel {
    return { type }
  }

  setId(model: TJsonaModel, id: string): void {
    Object.assign(model, { id })
  }

  setAttributes(model: TJsonaModel, attributes: TAnyKeyValueObject): void {
    const regex = new RegExp(this.caseRegExp, 'g')

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
        const regex = new RegExp(this.caseRegExp, 'g')
        const camelName = propName.replace(regex, g => g[1].toUpperCase())

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
          } else if (
            get(relation, 'type') === DeviceEntityTypes.DEVICE
          ) {
            Object.assign(model, { deviceId: get(relation, 'id') })
          } else if (
            get(relation, 'type') === ChannelEntityTypes.CHANNEL
          ) {
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
