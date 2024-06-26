<template>
	<el-dialog
		v-model="open"
		:show-close="false"
		align-center
		@closed="onClosed"
	>
		<template #header>
			<fb-dialog-header
				:layout="isXSDevice ? 'phone' : isSMDevice ? 'tablet' : 'default'"
				:left-btn-label="t('buttons.close.title')"
				:right-btn-label="isDraft ? t('buttons.add.title') : t('buttons.save.title')"
				:icon="FasPlus"
				@left-click="onClose"
				@right-click="onSubmitForm"
				@close="onClose"
			>
				<template #title>
					{{ t('headings.properties.add') }}
				</template>
			</fb-dialog-header>
		</template>

		<property-settings-property-form
			v-if="isConnectorProperty || props.property.type.type === PropertyType.VARIABLE"
			v-model:remote-form-submit="remoteFormSubmit"
			v-model:remote-form-result="remoteFormResult"
			:connector="props.connector"
			:device="props.device"
			:channel="props.channel"
			:property="props.property"
			@added="onAdded"
			@saved="onSaved"
		/>

		<template v-else>
			<template v-if="activeView === PropertySettingsPropertyAddModalViewTypes.SELECT_TYPE">
				<div class="mb-2 flex flex-row">
					<el-button
						:icon="FasFile"
						size="large"
						type="primary"
						class="w-full uppercase"
						@click.prevent="onOpenView(PropertySettingsPropertyAddModalViewTypes.NEW_PROPERTY)"
					>
						{{ t('buttons.new.title') }}
					</el-button>

					<el-button
						:icon="FasClone"
						size="large"
						type="primary"
						class="w-full uppercase"
						@click.prevent="onOpenView(PropertySettingsPropertyAddModalViewTypes.SELECT_CONNECTOR)"
					>
						{{ t('buttons.mapExisting.title') }}
					</el-button>
				</div>

				<el-alert
					type="info"
					:closable="false"
				>
					<h3>New parameter</h3>
					<p>This option will create new independent item parameter to receive or set data. This type of parameter could be fully customized.</p>

					<hr />

					<h3>Mapped parameter</h3>
					<p>
						This option will create parameter mapped to existing parameter. This type of parameter could not be configured, every settings is used
						from mapped parent one.
					</p>
				</el-alert>
			</template>

			<property-settings-property-form
				v-if="
					activeView === PropertySettingsPropertyAddModalViewTypes.NEW_PROPERTY ||
					activeView === PropertySettingsPropertyAddModalViewTypes.MAPPED_PROPERTY
				"
				v-model:remote-form-submit="remoteFormSubmit"
				v-model:remote-form-result="remoteFormResult"
				:connector="props.connector"
				:device="props.device"
				:channel="props.channel"
				:property="props.property"
				@added="onAdded"
				@saved="onSaved"
			/>

			<template v-if="activeView === PropertySettingsPropertyAddModalViewTypes.SELECT_CONNECTOR">
				<fb-list>
					<template #title>
						{{ t('headings.properties.connectorSelect') }}
					</template>

					<div class="py-2">
						<fb-list-item
							v-for="connectorItem in connectors"
							:key="connectorItem.id"
							:disabled="connectorItem.disabled"
							:variant="ListItemVariantTypes.DEFAULT"
							@click="onSelectConnector(connectorItem)"
						>
							<template #icon>
								<connectors-connector-icon :connector="connectorItem" />
							</template>

							<template #title>
								{{ useEntityTitle(connectorItem).value }}
							</template>

							<template
								v-if="connectorItem.hasComment"
								#subtitle
							>
								{{ connectorItem.comment }}
							</template>

							<template #button>
								<el-icon>
									<fas-chevron-right />
								</el-icon>
							</template>
						</fb-list-item>
					</div>

					<el-result v-if="!connectors.length">
						<template #primary>
							<fas-ethernet />
						</template>

						<template #secondary>
							<fas-exclamation />
						</template>

						<template #title>
							{{ t('texts.misc.noConnectors') }}
						</template>
					</el-result>
				</fb-list>
			</template>

			<template v-if="activeView === PropertySettingsPropertyAddModalViewTypes.SELECT_DEVICE">
				<fb-list>
					<template #title>
						{{ t('headings.properties.deviceSelect') }}
					</template>

					<div class="py-2">
						<fb-list-item
							v-for="deviceItem in devices"
							:key="deviceItem.id"
							:disabled="deviceItem.disabled"
							:variant="ListItemVariantTypes.DEFAULT"
							@click="onSelectDevice(deviceItem)"
						>
							<template #icon>
								<devices-device-icon :device="deviceItem" />
							</template>

							<template #title>
								{{ useEntityTitle(deviceItem).value }}
							</template>

							<template
								v-if="deviceItem.hasComment"
								#subtitle
							>
								{{ deviceItem.comment }}
							</template>

							<template #button>
								<el-icon>
									<fas-chevron-right />
								</el-icon>
							</template>
						</fb-list-item>
					</div>

					<el-result v-if="!devices.length">
						<template #primary>
							<fas-plug />
						</template>

						<template #secondary>
							<fas-exclamation />
						</template>

						<template #title>
							{{ t('texts.misc.noDevices') }}
						</template>
					</el-result>
				</fb-list>
			</template>

			<template v-if="activeView === PropertySettingsPropertyAddModalViewTypes.SELECT_CHANNEL">
				<fb-list>
					<template #title>
						{{ t('headings.properties.channelSelect') }}
					</template>

					<div class="py-2">
						<fb-list-item
							v-for="channelItem in channels"
							:key="channelItem.id"
							:disabled="channelItem.disabled"
							:variant="ListItemVariantTypes.DEFAULT"
							@click="onSelectChannel(channelItem)"
						>
							<template #icon>
								<fas-cube />
							</template>

							<template #title>
								{{ useEntityTitle(channelItem).value }}
							</template>

							<template
								v-if="channelItem.hasComment"
								#subtitle
							>
								{{ channelItem.comment }}
							</template>

							<template #button>
								<el-icon>
									<fas-chevron-right />
								</el-icon>
							</template>
						</fb-list-item>
					</div>

					<el-result v-if="!channels.length">
						<template #primary>
							<fas-cube />
						</template>

						<template #secondary>
							<fas-exclamation />
						</template>

						<template #title>
							{{ t('texts.devices.noChannels') }}
						</template>
					</el-result>
				</fb-list>
			</template>

			<template v-if="activeView === PropertySettingsPropertyAddModalViewTypes.SELECT_PARENT">
				<fb-list>
					<template #title>
						{{ t('headings.properties.parentSelect') }}
					</template>

					<div class="py-2">
						<fb-list-item
							v-for="propertyItem in properties"
							:key="propertyItem.id"
							:variant="ListItemVariantTypes.DEFAULT"
							@click="onSelectParent(propertyItem)"
						>
							<template #icon>
								<properties-property-icon :property="propertyItem" />
							</template>

							<template #title>
								{{ useEntityTitle(propertyItem).value }}
							</template>

							<template #button>
								<el-icon>
									<fas-chevron-right />
								</el-icon>
							</template>
						</fb-list-item>
					</div>

					<el-result v-if="!properties.length">
						<template #primary>
							<fas-cube />
						</template>

						<template #secondary>
							<fas-exclamation />
						</template>

						<template #title>
							<template v-if="isDeviceProperty">
								{{ t('texts.devices.noProperties') }}
							</template>

							<template v-if="isChannelProperty">
								{{ t('texts.channels.noProperties') }}
							</template>
						</template>
					</el-result>
				</fb-list>
			</template>
		</template>

		<template #footer>
			<fb-dialog-footer
				:left-btn-label="t('buttons.close.title')"
				:right-btn-label="isDraft ? t('buttons.add.title') : t('buttons.save.title')"
				@left-click="onClose"
				@right-click="onSubmitForm"
			>
				<template #left-button>
					<el-button
						v-if="
							!isConnectorProperty &&
							props.property.type.type !== PropertyType.VARIABLE &&
							(activeView === PropertySettingsPropertyAddModalViewTypes.NEW_PROPERTY ||
								activeView === PropertySettingsPropertyAddModalViewTypes.MAPPED_PROPERTY)
						"
						size="large"
						link
						name="cancel"
						class="uppercase"
						@click="onClose"
					>
						{{ t('buttons.cancel.title') }}
					</el-button>

					<el-button
						v-else
						size="large"
						link
						name="close"
						class="uppercase"
						@click="onClose"
					>
						{{ t('buttons.close.title') }}
					</el-button>
				</template>

				<template #right-button>
					<el-button
						v-if="isConnectorProperty || props.property.type.type === PropertyType.VARIABLE"
						:loading="remoteFormResult === FormResultTypes.WORKING"
						:disabled="remoteFormResult !== FormResultTypes.NONE"
						:icon="remoteFormResult === FormResultTypes.OK ? FarCircleCheck : remoteFormResult === FormResultTypes.ERROR ? FarCircleXmark : undefined"
						type="primary"
						size="large"
						name="submit"
						class="uppercase"
						@click="onSubmitForm"
					>
						{{ isDraft ? t('buttons.add.title') : t('buttons.save.title') }}
					</el-button>

					<template v-else>
						<template v-if="activeView === PropertySettingsPropertyAddModalViewTypes.SELECT_TYPE">
							<el-button
								type="primary"
								size="large"
								name="submit"
								class="uppercase"
								disabled
							>
								{{ isDraft ? t('buttons.add.title') : t('buttons.save.title') }}
							</el-button>
						</template>

						<template
							v-if="
								activeView === PropertySettingsPropertyAddModalViewTypes.NEW_PROPERTY ||
								activeView === PropertySettingsPropertyAddModalViewTypes.MAPPED_PROPERTY
							"
						>
							<el-button
								:loading="remoteFormResult === FormResultTypes.WORKING"
								:disabled="remoteFormResult !== FormResultTypes.NONE"
								:icon="
									remoteFormResult === FormResultTypes.OK ? FarCircleCheck : remoteFormResult === FormResultTypes.ERROR ? FarCircleXmark : undefined
								"
								type="primary"
								size="large"
								name="submit"
								class="uppercase"
								@click="onSubmitForm"
							>
								{{ isDraft ? t('buttons.add.title') : t('buttons.save.title') }}
							</el-button>
						</template>

						<template
							v-if="
								activeView === PropertySettingsPropertyAddModalViewTypes.SELECT_CONNECTOR ||
								activeView === PropertySettingsPropertyAddModalViewTypes.SELECT_DEVICE ||
								activeView === PropertySettingsPropertyAddModalViewTypes.SELECT_CHANNEL ||
								activeView === PropertySettingsPropertyAddModalViewTypes.SELECT_PARENT
							"
						>
							<el-button
								type="primary"
								size="large"
								name="submit"
								class="uppercase"
								@click="onBack"
							>
								{{ t('buttons.back.title') }}
							</el-button>
						</template>
					</template>
				</template>
			</fb-dialog-footer>
		</template>
	</el-dialog>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { orderBy } from 'natural-orderby';
import { ElAlert, ElButton, ElDialog, ElIcon, ElResult } from 'element-plus';

import {
	FasPlus,
	FasFile,
	FasClone,
	FasChevronRight,
	FasEthernet,
	FasExclamation,
	FasPlug,
	FasCube,
	FarCircleCheck,
	FarCircleXmark,
} from '@fastybird/web-ui-icons';
import { FbDialogHeader, FbDialogFooter, FbList, FbListItem, ListItemVariantTypes } from '@fastybird/web-ui-library';
import { PropertyType } from '@fastybird/metadata-library';

import { useBreakpoints, useEntityTitle } from '../../composables';
import { useChannelProperties, useChannels, useConnectorProperties, useConnectors, useDeviceProperties, useDevices } from '../../models';
import { IChannel, IChannelProperty, IConnector, IConnectorProperty, IDevice, IDeviceProperty } from '../../models/types';
import { ConnectorsConnectorIcon, DevicesDeviceIcon, PropertiesPropertyIcon, PropertySettingsPropertyForm } from '../../components';
import { FormResultTypes } from '../../types';
import {
	IChannelListItem,
	IConnectorListItem,
	IDeviceListItem,
	IPropertySettingsPropertyAddModalProps,
	PropertySettingsPropertyAddModalViewTypes,
} from './property-settings-property-add-modal.types';

defineOptions({
	name: 'PropertySettingsPropertyAddModal',
});

const props = defineProps<IPropertySettingsPropertyAddModalProps>();

const emit = defineEmits<{
	(e: 'close', canceled: boolean): void;
}>();

const { t } = useI18n();
const { isXSDevice, isSMDevice } = useBreakpoints();

const connectorsStore = useConnectors();
const connectorPropertiesStore = useConnectorProperties();
const devicesStore = useDevices();
const devicePropertiesStore = useDeviceProperties();
const channelsStore = useChannels();
const channelPropertiesStore = useChannelProperties();

const open = ref<boolean>(true);

let closedCallback: () => void = (): void => {};

const remoteFormSubmit = ref<boolean>(false);
const remoteFormResult = ref<FormResultTypes>(FormResultTypes.NONE);

const activeView = ref<PropertySettingsPropertyAddModalViewTypes>(PropertySettingsPropertyAddModalViewTypes.SELECT_TYPE);

const isDraft = computed<boolean>((): boolean => {
	if (isChannelProperty.value) {
		return props.channel ? props.channel.draft : false;
	}

	if (isDeviceProperty.value) {
		return props.device ? props.device.draft : false;
	}

	if (isConnectorProperty.value) {
		return props.connector ? props.connector.draft : false;
	}

	return false;
});

const isConnectorProperty = computed<boolean>((): boolean => props.connector !== undefined);
const isDeviceProperty = computed<boolean>((): boolean => props.device !== undefined && props.channel === undefined);
const isChannelProperty = computed<boolean>((): boolean => props.device !== undefined && props.channel !== undefined);

const selectedConnector = ref<IConnector | null>(null);
const selectedDevice = ref<IDevice | null>(null);
const selectedChannel = ref<IChannel | null>(null);

const connectors = computed<IConnectorListItem[]>((): IConnectorListItem[] => {
	return orderBy<IConnectorListItem>(
		Object.values(connectorsStore.data ?? {})
			.filter((connector) => !connector.draft)
			.map((connector) => {
				if (!isConnectorProperty.value) {
					return { ...connector, ...{ disabled: false } };
				}

				return { ...connector, ...{ disabled: !('connector' in props.property && connector.id !== props.property.connector.id) } };
			}),
		[(v): string => v.name ?? v.identifier, (v): string => v.identifier],
		['asc']
	);
});

const devices = computed<IDeviceListItem[]>((): IDeviceListItem[] => {
	if (selectedConnector.value === null) {
		return [];
	}

	return orderBy<IDeviceListItem>(
		Object.values(devicesStore.findForConnector(selectedConnector.value.id))
			.filter((device) => !device.draft)
			.map((device) => {
				if (!isDeviceProperty.value) {
					return { ...device, ...{ disabled: false } };
				}

				return { ...device, ...{ disabled: !('device' in props.property && device.id !== props.property.device.id) } };
			}),
		[(v): string => v.name ?? v.identifier, (v): string => v.identifier],
		['asc']
	);
});

const channels = computed<IChannelListItem[]>((): IChannelListItem[] => {
	if (selectedDevice.value === null) {
		return [];
	}

	return orderBy<IChannelListItem>(
		Object.values(channelsStore.findForDevice(selectedDevice.value.id))
			.filter((channel) => !channel.draft)
			.map((channel) => {
				if (!isChannelProperty.value) {
					return { ...channel, ...{ disabled: false } };
				}

				return { ...channel, ...{ disabled: !('channel' in props.property && channel.id !== props.property.channel.id) } };
			}),
		[(v): string => v.name ?? v.identifier, (v): string => v.identifier],
		['asc']
	);
});

const properties = computed<(IChannelProperty | IConnectorProperty | IDeviceProperty)[]>(
	(): (IChannelProperty | IConnectorProperty | IDeviceProperty)[] => {
		if (isChannelProperty.value) {
			if (selectedChannel.value === null) {
				return [];
			}

			return orderBy<IChannelProperty>(
				Object.values(channelPropertiesStore.findForChannel(selectedChannel.value.id)).filter(
					(property) => !property.draft && property.type.type === props.property.type.type && property.parent === null
				),
				[(v): string => v.name ?? v.identifier, (v): string => v.identifier],
				['asc']
			);
		}

		if (isDeviceProperty.value) {
			if (selectedDevice.value === null) {
				return [];
			}

			return orderBy<IDeviceProperty>(
				Object.values(devicePropertiesStore.findForDevice(selectedDevice.value.id))
					.filter((property) => !property.draft)
					.filter((property) => property.type.type === props.property.type.type),
				[(v): string => v.name ?? v.identifier, (v): string => v.identifier],
				['asc']
			);
		}

		if (isConnectorProperty.value) {
			if (selectedConnector.value === null) {
				return [];
			}

			return orderBy<IConnectorProperty>(
				Object.values(connectorPropertiesStore.findForConnector(selectedConnector.value.id))
					.filter((property) => !property.draft)
					.filter((property) => property.type.type === props.property.type.type),
				[(v): string => v.name ?? v.identifier, (v): string => v.identifier],
				['asc']
			);
		}

		return [];
	}
);

const onSelectConnector = (connector: IConnectorListItem): void => {
	if (connector.disabled) {
		return;
	}

	selectedConnector.value = connector;

	activeView.value = isConnectorProperty.value
		? PropertySettingsPropertyAddModalViewTypes.SELECT_PARENT
		: PropertySettingsPropertyAddModalViewTypes.SELECT_DEVICE;
};

const onSelectDevice = (device: IDeviceListItem): void => {
	if (device.disabled) {
		return;
	}

	selectedDevice.value = device;

	activeView.value = isDeviceProperty.value
		? PropertySettingsPropertyAddModalViewTypes.SELECT_PARENT
		: PropertySettingsPropertyAddModalViewTypes.SELECT_CHANNEL;
};

const onSelectChannel = (channel: IChannelListItem): void => {
	if (channel.disabled) {
		return;
	}

	selectedChannel.value = channel;

	activeView.value = PropertySettingsPropertyAddModalViewTypes.SELECT_PARENT;
};

const onSelectParent = (property: IChannelProperty | IConnectorProperty | IDeviceProperty): void => {
	if (isDeviceProperty.value) {
		devicePropertiesStore.edit({
			id: props.property.id,
			parent: property as IDeviceProperty,
			data: {},
		});

		activeView.value = PropertySettingsPropertyAddModalViewTypes.MAPPED_PROPERTY;
	} else if (isChannelProperty.value) {
		channelPropertiesStore.edit({
			id: props.property.id,
			parent: property as IChannelProperty,
			data: {},
		});

		activeView.value = PropertySettingsPropertyAddModalViewTypes.MAPPED_PROPERTY;
	}
};

const onSubmitForm = (): void => {
	remoteFormSubmit.value = true;
};

const onClose = (): void => {
	closedCallback = (): void => emit('close', true);
	open.value = false;
};

const onClosed = (): void => {
	closedCallback();
};

const onBack = (): void => {
	if (activeView.value === PropertySettingsPropertyAddModalViewTypes.SELECT_CONNECTOR) {
		activeView.value = PropertySettingsPropertyAddModalViewTypes.SELECT_TYPE;
	} else if (activeView.value === PropertySettingsPropertyAddModalViewTypes.SELECT_DEVICE) {
		activeView.value = PropertySettingsPropertyAddModalViewTypes.SELECT_CONNECTOR;
	} else if (activeView.value === PropertySettingsPropertyAddModalViewTypes.SELECT_CHANNEL) {
		activeView.value = PropertySettingsPropertyAddModalViewTypes.SELECT_DEVICE;
	} else if (activeView.value === PropertySettingsPropertyAddModalViewTypes.SELECT_PARENT) {
		if (isChannelProperty.value) {
			activeView.value = PropertySettingsPropertyAddModalViewTypes.SELECT_CHANNEL;
		} else if (isDeviceProperty.value) {
			activeView.value = PropertySettingsPropertyAddModalViewTypes.SELECT_DEVICE;
		} else if (isConnectorProperty.value) {
			activeView.value = PropertySettingsPropertyAddModalViewTypes.SELECT_CONNECTOR;
		}
	}
};

const onOpenView = (view: PropertySettingsPropertyAddModalViewTypes): void => {
	activeView.value = view;
};

const onAdded = (): void => {
	closedCallback = (): void => emit('close', false);
	open.value = false;
};

const onSaved = (): void => {
	closedCallback = (): void => emit('close', false);
	open.value = false;
};
</script>
