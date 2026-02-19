<template>
	<NcContent app-name="tramita">
		<NcAppContent>
			<LicenseWarning v-if="licenseRequired" :show="licenseRequired" />
			<router-view v-else />
		</NcAppContent>
	</NcContent>
</template>

<script>
import { NcContent, NcAppContent } from '@nextcloud/vue'
import LicenseWarning from './components/common/LicenseWarning.vue'

export default {
	name: 'App',
	components: {
		NcContent,
		NcAppContent,
		LicenseWarning,
	},
	data() {
		return {
			licenseRequired: false,
		}
	},
	created() {
		this._licenseHandler = () => {
			this.licenseRequired = true
		}
		window.addEventListener('tramita:license-required', this._licenseHandler)
	},
	beforeDestroy() {
		window.removeEventListener('tramita:license-required', this._licenseHandler)
	},
}
</script>

<style lang="scss">
.app-tramita {
	height: 100%;

	#app-content {
		height: 100%;
	}
}
</style>
