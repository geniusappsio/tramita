<template>
	<div class="tramita-admin">
		<div v-if="showBack" class="tramita-admin__back">
			<NcButton type="tertiary" @click="$router.push({ name: 'processTypes' })">
				<template #icon>
					<ArrowLeft :size="20" />
				</template>
				Voltar para Tipos de Processo
			</NcButton>
		</div>
		<router-view />
	</div>
</template>

<script>
import NcButton from '@nextcloud/vue/dist/Components/NcButton.js'
import ArrowLeft from 'vue-material-design-icons/ArrowLeft.vue'

export default {
	name: 'AdminSettings',
	components: { NcButton, ArrowLeft },
	computed: {
		showBack() {
			return this.$route.name !== 'processTypes'
		},
	},
}
</script>

<style lang="scss" scoped>
.tramita-admin {
	padding: 0;

	&__back {
		margin-bottom: 16px;
	}
}
</style>
