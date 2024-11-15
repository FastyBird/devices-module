import { computed } from 'vue';

import { breakpointsBootstrapV5, useBreakpoints as vueUseBreakpoints } from '@vueuse/core';

import { UseBreakpoints } from './types';

const breakpoints = vueUseBreakpoints(breakpointsBootstrapV5);

export function useBreakpoints(): UseBreakpoints {
	const isXSDevice = computed<boolean>((): boolean => breakpoints.xs.value);
	const isSMDevice = computed<boolean>((): boolean => breakpoints.sm.value);
	const isMDDevice = computed<boolean>((): boolean => breakpoints.md.value);
	const isLGDevice = computed<boolean>((): boolean => breakpoints.lg.value);
	const isXLDevice = computed<boolean>((): boolean => breakpoints.xl.value);
	const isXXLDevice = computed<boolean>((): boolean => breakpoints.xxl.value);

	return {
		isXSDevice,
		isSMDevice,
		isMDDevice,
		isLGDevice,
		isXLDevice,
		isXXLDevice,
	};
}
