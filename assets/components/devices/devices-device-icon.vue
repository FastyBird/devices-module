<template>
	<fb-icon-with-child
		v-if="props.withState"
		:type="stateColor"
		:size="props.size"
		:data-connector-state="stateName"
	>
		<template #primary>
			<fas-plug />
		</template>
		<template #secondary>
			<component :is="stateIcon" />
		</template>
	</fb-icon-with-child>

	<el-icon
		v-else
		:size="props.size"
	>
		<fas-plug />
	</el-icon>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { ElIcon } from 'element-plus';

import {
	FasPlug,
	FarCirclePause,
	FarCircleStop,
	FasCircleExclamation,
	FarCircleQuestion,
	FarCircleUser,
	FarCirclePlay,
	FarCircle,
} from '@fastybird/web-ui-icons';
import { FbIconWithChild } from '@fastybird/web-ui-library';
import { ConnectionState } from '@fastybird/metadata-library';
import { useWampV1Client } from '@fastybird/vue-wamp-v1';

import { useDeviceState } from '../../composables';
import { IDevicesIconProps } from './devices-device-icon.types';

import type { Component } from 'vue';

defineOptions({
	name: 'DevicesDeviceIcon',
});

const props = withDefaults(defineProps<IDevicesIconProps>(), {
	withState: false,
});

const { status: wsStatus } = useWampV1Client();

const { state: deviceState } = useDeviceState(props.device);

const stateIcon = computed<Component>((): Component => {
	if (!wsStatus || deviceState.value === ConnectionState.SLEEPING) {
		return FarCirclePause;
	} else if ([ConnectionState.STOPPED, ConnectionState.DISCONNECTED].includes(deviceState.value)) {
		return FarCircleStop;
	} else if (deviceState.value === ConnectionState.ALERT) {
		return FasCircleExclamation;
	} else if (deviceState.value === ConnectionState.LOST) {
		return FarCircleQuestion;
	} else if (deviceState.value === ConnectionState.INIT) {
		return FarCircleUser;
	} else if ([ConnectionState.RUNNING, ConnectionState.READY, ConnectionState.CONNECTED].includes(deviceState.value)) {
		return FarCirclePlay;
	}

	return FarCircle;
});

const stateName = computed<string>((): string => {
	if (!wsStatus || deviceState.value === ConnectionState.SLEEPING) {
		return 'pause';
	} else if ([ConnectionState.STOPPED, ConnectionState.DISCONNECTED].includes(deviceState.value)) {
		return 'stop';
	} else if (deviceState.value === ConnectionState.ALERT) {
		return 'alert';
	} else if (deviceState.value === ConnectionState.LOST) {
		return 'lost';
	} else if (deviceState.value === ConnectionState.INIT) {
		return 'init';
	} else if ([ConnectionState.RUNNING, ConnectionState.READY].includes(deviceState.value)) {
		return 'ready';
	} else if (deviceState.value === ConnectionState.CONNECTED) {
		return 'connected';
	}

	return 'unknown';
});

const stateColor = computed<string>((): string => {
	if (!wsStatus || deviceState.value === ConnectionState.SLEEPING) {
		return 'warning';
	} else if (deviceState.value === ConnectionState.ALERT) {
		return 'danger';
	} else if (deviceState.value === ConnectionState.INIT) {
		return 'info';
	} else if ([ConnectionState.RUNNING, ConnectionState.READY, ConnectionState.CONNECTED].includes(deviceState.value)) {
		return 'success';
	}

	return 'primary';
});
</script>
