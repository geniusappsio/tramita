<template>
	<div class="dashboard">
		<div class="dashboard__header">
			<h2>Tramita</h2>
			<p>Gestão de processos e tramitação de documentos</p>
		</div>

		<NcLoadingIcon v-if="store.loading" :size="44" />

		<NcEmptyContent v-else-if="store.processTypes.length === 0"
			name="Bem-vindo ao Tramita"
			description="Configure seu primeiro tipo de processo para começar.">
			<template #icon>
				<FolderIcon :size="20" />
			</template>
			<template #action>
				<NcButton type="primary" @click="$router.push('/process-types')">
					Configurar Tipos de Processo
				</NcButton>
			</template>
		</NcEmptyContent>

		<div v-else class="dashboard__grid">
			<div v-for="pt in store.activeProcessTypes"
				:key="pt.id"
				class="dashboard-card"
				@click="$router.push(`/board/${pt.id}`)">
				<div class="dashboard-card__color" :style="{ backgroundColor: pt.color || '#0082c9' }" />
				<div class="dashboard-card__info">
					<h3>{{ pt.name }}</h3>
					<span class="dashboard-card__prefix">{{ pt.prefix }}</span>
					<p v-if="pt.description">{{ pt.description }}</p>
				</div>
				<div class="dashboard-card__arrow">
					<ChevronRight :size="24" />
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import NcButton from '@nextcloud/vue/dist/Components/NcButton.js'
import NcEmptyContent from '@nextcloud/vue/dist/Components/NcEmptyContent.js'
import NcLoadingIcon from '@nextcloud/vue/dist/Components/NcLoadingIcon.js'
import FolderIcon from 'vue-material-design-icons/Folder.vue'
import ChevronRight from 'vue-material-design-icons/ChevronRight.vue'
import { useProcessTypeStore } from '../store/processType.js'
import { showError } from '@nextcloud/dialogs'

export default {
	name: 'Dashboard',
	components: {
		NcButton,
		NcEmptyContent,
		NcLoadingIcon,
		FolderIcon,
		ChevronRight,
	},
	data() {
		return {
			store: useProcessTypeStore(),
		}
	},
	async created() {
		try {
			await this.store.fetchAll()
		} catch (error) {
			showError('Erro ao carregar tipos de processo')
		}
	},
}
</script>

<style scoped lang="scss">
.dashboard {
	padding: 20px;
	max-width: 900px;
	margin: 0 auto;

	&__header {
		margin-bottom: 32px;

		h2 {
			font-size: 28px;
			font-weight: 700;
			margin: 0 0 4px;
		}

		p {
			color: var(--color-text-maxcontrast);
			margin: 0;
			font-size: 15px;
		}
	}

	&__grid {
		display: flex;
		flex-direction: column;
		gap: 12px;
	}
}

.dashboard-card {
	display: flex;
	align-items: center;
	background: var(--color-main-background);
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-large);
	padding: 16px 20px;
	cursor: pointer;
	transition: box-shadow 0.2s, border-color 0.2s;

	&:hover {
		box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
		border-color: var(--color-primary-element);
	}

	&__color {
		width: 8px;
		height: 48px;
		border-radius: 4px;
		flex-shrink: 0;
		margin-right: 16px;
	}

	&__info {
		flex: 1;
		min-width: 0;

		h3 {
			margin: 0 0 2px;
			font-size: 16px;
			font-weight: 600;
		}

		p {
			margin: 4px 0 0;
			font-size: 13px;
			color: var(--color-text-maxcontrast);
			white-space: nowrap;
			overflow: hidden;
			text-overflow: ellipsis;
		}
	}

	&__prefix {
		font-size: 11px;
		font-family: monospace;
		color: var(--color-text-maxcontrast);
		background: var(--color-background-dark);
		padding: 1px 6px;
		border-radius: var(--border-radius);
	}

	&__arrow {
		flex-shrink: 0;
		color: var(--color-text-maxcontrast);
		margin-left: 12px;
	}
}
</style>
