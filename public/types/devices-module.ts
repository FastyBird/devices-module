import { Database, Model } from '@vuex-orm/core'

export interface GlobalConfigInterface {
  database: Database
  originName?: string
}

export interface ComponentsInterface {
  Model: typeof Model
}

declare module '@vuex-orm/core' {
  // eslint-disable-next-line @typescript-eslint/no-namespace
  namespace Model {
    // Exchange origin name
    const $devicesModuleOrigin: string
  }
}

// Re-export models types
export * from '@/lib/types'
export * from '@/lib/models/channel-configuration/types'
export * from '@/lib/models/channel-properties/types'
export * from '@/lib/models/channels/types'
export * from '@/lib/models/configuration/types'
export * from '@/lib/models/connectors/types'
export * from '@/lib/models/device-configuration/types'
export * from '@/lib/models/device-connector/types'
export * from '@/lib/models/device-properties/types'
export * from '@/lib/models/devices/types'
export * from '@/lib/models/properties/types'
