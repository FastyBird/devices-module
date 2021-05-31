import { Database, Model } from '@vuex-orm/core'

interface VuexOrmComponentsInterface {
    Database: Database
    Model: Model
}

declare const _default: {
    install(components: VuexOrmComponentsInterface, options: {
        database: Database
        originName?: string
    }): void
}

export default _default;

declare module '@vuex-orm/core' {
    namespace Model {
        /**
         * Exchange origin name
         */
        const $devicesModuleOrigin: string;
    }
}
