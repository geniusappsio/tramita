<template>
	<div v-if="show" class="license-warning">
		<div class="license-warning__icon">
			<AlertCircle :size="24" />
		</div>
		<div class="license-warning__content">
			<h3>Licença Inválida</h3>
			<p>Sua licença do Tramita expirou ou é inválida. Entre em contato com a Genius Apps para renovar.</p>
			<a href="mailto:contato@geniusapps.com.br">contato@geniusapps.com.br</a>
		</div>
	</div>
</template>

<script>
import AlertCircle from 'vue-material-design-icons/AlertCircle.vue'

export default {
	name: 'LicenseWarning',
	components: { AlertCircle },
	props: {
		show: { type: Boolean, default: false },
	},
}
</script>

<style scoped lang="scss">
.license-warning {
	display: flex;
	gap: 12px;
	padding: 16px;
	margin: 16px;
	background: var(--color-warning-hover, #fff3cd);
	border: 1px solid var(--color-warning, #ffc107);
	border-radius: var(--border-radius-large);
	align-items: flex-start;

	&__icon {
		color: var(--color-warning);
		flex-shrink: 0;
	}

	&__content {
		h3 {
			margin: 0 0 4px;
			font-size: 15px;
		}

		p {
			margin: 0 0 4px;
			font-size: 13px;
			color: var(--color-text-maxcontrast);
		}

		a {
			font-size: 13px;
			color: var(--color-primary-element);
		}
	}
}
</style>
