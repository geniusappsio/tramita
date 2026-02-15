<template>
	<NcContent app-name="tramita">
		<NcAppContent>
			<router-view />
		</NcAppContent>
	</NcContent>
</template>

<script>
import { NcContent, NcAppContent } from '@nextcloud/vue'

export default {
	name: 'App',
	components: {
		NcContent,
		NcAppContent,
	},
}
</script>

<style lang="scss">
.app-tramita {
	min-height: 100%;
}
</style>
