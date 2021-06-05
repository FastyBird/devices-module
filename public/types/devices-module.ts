import { Database, Model } from '@vuex-orm/core'
import { Plugin } from '@vuex-orm/core/dist/src/plugins/use'

export interface InstallFunction extends Plugin {
}

export interface GlobalConfigInterface {
  database: Database
  originName?: string
}

export interface ComponentsInterface {
  Model: typeof Model
}

declare module '@vuex-orm/core' {
  namespace Model {
    // Exchange origin name
    const $devicesModuleOrigin: string
  }
}

// Re-export models types
export * from '@/lib/channel-configuration/types'
export * from '@/lib/channel-properties/types'
export * from '@/lib/channels/types'
export * from '@/lib/configuration/types'
export * from '@/lib/connectors/types'
export * from '@/lib/device-configuration/types'
export * from '@/lib/device-connector/types'
export * from '@/lib/device-properties/types'
export * from '@/lib/devices/types'
export * from '@/lib/properties/types'
