import { resolve } from 'path';
import UnoCSS from 'unocss/vite';
import { defineConfig } from 'vite';
import dts from 'vite-plugin-dts';
import vueTypeImports from 'vite-plugin-vue-type-imports';

import vueI18n from '@intlify/unplugin-vue-i18n/vite';
import eslint from '@nabla/vite-plugin-eslint';
import vue from '@vitejs/plugin-vue';

// https://vitejs.dev/config/
export default defineConfig({
	plugins: [
		vue(),
		vueTypeImports(),
		vueI18n({
			include: [resolve(__dirname, './locales/**.json')],
		}),
		eslint(),
		dts({
			outDir: 'dist',
			staticImport: true,
			insertTypesEntry: true,
			rollupTypes: true,
		}),
		UnoCSS(),
	],
	build: {
		lib: {
			entry: resolve(__dirname, './assets/entry.ts'),
			name: 'devices-module',
			fileName: (format) => `devices-module.${format}.js`,
		},
		rollupOptions: {
			external: [
				'@fastybird/metadata-library',
				'@fastybird/tools',
				'@fastybird/vue-wamp-v1',
				'@fastybird/web-ui-icons',
				'@fastybird/web-ui-library',
				'axios',
				'element-plus',
				'pinia',
				'unocss',
				'vue',
				'vue-i18n',
				'vue-meta',
				'vue-router',
			],
			output: {
				assetFileNames: (chunkInfo) => {
					if (chunkInfo.name == 'style.css') return 'devices-module.css';

					return chunkInfo.name as string;
				},
				exports: 'named',
			},
		},
		sourcemap: true,
		target: 'esnext',
	},
});
