<template>
	<div class="flex flex-col justify-center h-full">
		<fb-media-item class="my-5">
			<template #left>
				<el-icon :size="32">
					<fas-plug-circle-bolt />
				</el-icon>
			</template>

			<template #heading>
				{{ t('devicesModule.headings.plugins.allPlugins') }}
			</template>

			<template #description>
				{{ t('devicesModule.subHeadings.plugins.allPlugins', items.length) }}
			</template>
		</fb-media-item>

		<fb-media-item class="my-5">
			<template #left>
				<el-icon :size="32">
					<fas-ethernet />
				</el-icon>
			</template>

			<template #heading>
				{{ t('devicesModule.headings.connectors.allConnectors') }}
			</template>

			<template #description>
				{{ t('devicesModule.subHeadings.connectors.allConnectors', connectors) }}
			</template>
		</fb-media-item>

		<fb-media-item class="my-5">
			<template #left>
				<el-icon :size="32">
					<fas-plus />
				</el-icon>
			</template>

			<template #heading>
				{{ t('devicesModule.headings.connectors.new') }}
			</template>

			<template #description>
				{{ t('devicesModule.subHeadings.connectors.new') }}
			</template>

			<template #action>
				<el-button
					:icon="FasPlus"
					type="primary"
					plain
					@click="emit('addConnector', $event)"
				>
					{{ t('devicesModule.buttons.addInstance.title') }}
				</el-button>
			</template>
		</fb-media-item>

		<fb-media-item class="my-5">
			<template #left>
				<el-icon :size="32">
					<fas-store />
				</el-icon>
			</template>

			<template #heading>
				{{ t('devicesModule.headings.plugins.new') }}
			</template>

			<template #description>
				{{ t('devicesModule.subHeadings.plugins.new') }}
			</template>

			<template #action>
				<el-button
					:icon="FasPlus"
					type="info"
					plain
					@click="emit('installPlugin', $event)"
				>
					{{ t('devicesModule.buttons.addPlugin.title') }}
				</el-button>
			</template>
		</fb-media-item>
	</div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

import { ElButton, ElIcon } from 'element-plus';

import { FasEthernet, FasPlugCircleBolt, FasPlus, FasStore } from '@fastybird/web-ui-icons';
import { FbMediaItem } from '@fastybird/web-ui-library';

import { IPluginsPreviewInfoProps } from './plugins-preview-info.types';

defineOptions({
	name: 'PluginsPreviewInfo',
});

const props = defineProps<IPluginsPreviewInfoProps>();

const emit = defineEmits<{
	(e: 'installPlugin', event: Event): void;
	(e: 'addConnector', event: Event): void;
}>();

const { t } = useI18n();

const connectors = computed<number>((): number => {
	let cnt = 0;

	for (const item of props.items) {
		cnt += item.connectors.length;
	}

	return cnt;
});
</script>
