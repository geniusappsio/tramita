<template>
	<div class="request-detail">
		<div class="request-detail__nav">
			<NcButton type="tertiary" @click="$router.back()">
				<template #icon>
					<ArrowLeft :size="20" />
				</template>
				Voltar
			</NcButton>
		</div>

		<NcLoadingIcon v-if="loading" :size="44" />

		<template v-else-if="request">
			<div class="request-detail__header">
				<div>
					<span v-if="request.protocolId" class="protocol-badge">
						{{ request.protocolNumber || `#${request.protocolId}` }}
					</span>
					<h2>{{ request.title }}</h2>
				</div>
				<div class="request-detail__actions">
					<NcButton type="secondary" @click="editRequest">
						<template #icon>
							<Pencil :size="20" />
						</template>
						Editar
					</NcButton>
				</div>
			</div>

			<div class="request-detail__body">
				<div class="request-detail__main">
					<div class="detail-section">
						<h3>Descrição</h3>
						<p v-if="request.description">{{ request.description }}</p>
						<p v-else class="text-muted">Sem descrição</p>
					</div>

					<div class="detail-section">
						<h3>Histórico</h3>
						<p class="text-muted">Histórico de atividades em breve...</p>
					</div>
				</div>

				<div class="request-detail__sidebar">
					<div class="sidebar-section">
						<h4>Status</h4>
						<span :class="['status-badge', `status-badge--${request.status}`]">
							{{ statusLabel }}
						</span>
					</div>
					<div class="sidebar-section">
						<h4>Prioridade</h4>
						<span :class="['priority-badge', `priority--${request.priority}`]">
							{{ priorityLabel }}
						</span>
					</div>
					<div class="sidebar-section">
						<h4>Etapa Atual</h4>
						<span>{{ currentStageName }}</span>
					</div>
					<div class="sidebar-section">
						<h4>Solicitante</h4>
						<span>{{ request.requesterName || request.requesterId }}</span>
					</div>
					<div v-if="request.dueDate" class="sidebar-section">
						<h4>Prazo</h4>
						<span :class="{ 'text-error': isOverdue }">{{ formatDate(request.dueDate) }}</span>
					</div>
					<div class="sidebar-section">
						<h4>Criado em</h4>
						<span>{{ formatDateTime(request.createdAt) }}</span>
					</div>
					<div class="sidebar-section">
						<h4>Atribuídos</h4>
						<p class="text-muted">Em breve...</p>
					</div>
					<div class="sidebar-section">
						<h4>Labels</h4>
						<p class="text-muted">Em breve...</p>
					</div>
				</div>
			</div>
		</template>
	</div>
</template>

<script>
import NcButton from '@nextcloud/vue/dist/Components/NcButton.js'
import NcLoadingIcon from '@nextcloud/vue/dist/Components/NcLoadingIcon.js'
import ArrowLeft from 'vue-material-design-icons/ArrowLeft.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import { useRequestStore } from '../store/request.js'
import { useStageStore } from '../store/stage.js'
import { showError } from '@nextcloud/dialogs'

export default {
	name: 'RequestDetail',
	components: {
		NcButton,
		NcLoadingIcon,
		ArrowLeft,
		Pencil,
	},
	data() {
		return {
			requestStore: useRequestStore(),
			stageStore: useStageStore(),
			loading: true,
		}
	},
	computed: {
		requestId() {
			return parseInt(this.$route.params.id)
		},
		request() {
			return this.requestStore.getById(this.requestId)
		},
		statusLabel() {
			if (!this.request) return ''
			const labels = {
				open: 'Aberto',
				in_progress: 'Em Andamento',
				completed: 'Concluído',
				cancelled: 'Cancelado',
				on_hold: 'Em Espera',
			}
			return labels[this.request.status] || this.request.status
		},
		priorityLabel() {
			if (!this.request) return ''
			const labels = { 1: 'Urgente', 2: 'Normal', 3: 'Baixa' }
			return labels[this.request.priority] || 'Normal'
		},
		currentStageName() {
			if (!this.request?.stageId) return 'Não definida'
			const stage = this.stageStore.getById(this.request.stageId)
			return stage?.name || 'Desconhecida'
		},
		isOverdue() {
			if (!this.request?.dueDate) return false
			return new Date(this.request.dueDate) < new Date()
		},
	},
	async created() {
		try {
			await this.requestStore.fetchById(this.requestId)
			// Fetch stage data if we have a process type
			if (this.request?.procTypeId) {
				await this.stageStore.fetchByProcessType(this.request.procTypeId)
			}
		} catch (error) {
			showError('Erro ao carregar requisição')
		} finally {
			this.loading = false
		}
	},
	methods: {
		formatDate(dateStr) {
			if (!dateStr) return ''
			const date = new Date(dateStr)
			return date.toLocaleDateString('pt-BR')
		},
		formatDateTime(dateStr) {
			if (!dateStr) return ''
			const date = new Date(dateStr)
			return date.toLocaleDateString('pt-BR', {
				day: '2-digit',
				month: '2-digit',
				year: 'numeric',
				hour: '2-digit',
				minute: '2-digit',
			})
		},
		editRequest() {
			this.$router.push(`/request/${this.requestId}/edit`)
		},
	},
}
</script>

<style scoped lang="scss">
.request-detail {
	padding: 20px;
	max-width: 1200px;
	margin: 0 auto;

	&__nav {
		margin-bottom: 16px;
	}

	&__header {
		display: flex;
		justify-content: space-between;
		align-items: flex-start;
		margin-bottom: 24px;

		h2 {
			margin: 4px 0 0;
			font-size: 22px;
		}
	}

	&__actions {
		display: flex;
		gap: 8px;
		flex-shrink: 0;
	}

	&__body {
		display: flex;
		gap: 24px;
	}

	&__main {
		flex: 1;
		min-width: 0;
	}

	&__sidebar {
		width: 280px;
		flex-shrink: 0;
		background: var(--color-background-dark);
		border-radius: var(--border-radius-large);
		padding: 16px;
	}
}

.protocol-badge {
	font-size: 12px;
	font-family: monospace;
	color: var(--color-text-maxcontrast);
	background: var(--color-background-dark);
	padding: 2px 8px;
	border-radius: var(--border-radius);
}

.detail-section {
	margin-bottom: 24px;

	h3 {
		font-size: 16px;
		font-weight: 600;
		margin: 0 0 12px;
		padding-bottom: 8px;
		border-bottom: 1px solid var(--color-border);
	}

	p {
		margin: 0;
		line-height: 1.6;
	}
}

.sidebar-section {
	margin-bottom: 16px;
	padding-bottom: 16px;
	border-bottom: 1px solid var(--color-border);

	&:last-child {
		border-bottom: none;
		margin-bottom: 0;
		padding-bottom: 0;
	}

	h4 {
		margin: 0 0 4px;
		font-size: 12px;
		font-weight: 600;
		color: var(--color-text-maxcontrast);
		text-transform: uppercase;
		letter-spacing: 0.5px;
	}

	span, p {
		font-size: 14px;
		margin: 0;
	}
}

.status-badge {
	display: inline-block;
	padding: 2px 10px;
	border-radius: var(--border-radius-pill);
	font-size: 12px;
	font-weight: 600;

	&--open {
		background: var(--color-primary-element-light);
		color: var(--color-primary-element);
	}

	&--in_progress {
		background: var(--color-warning);
		color: white;
	}

	&--completed {
		background: var(--color-success);
		color: white;
	}

	&--cancelled {
		background: var(--color-text-maxcontrast);
		color: white;
	}

	&--on_hold {
		background: var(--color-background-darker);
		color: var(--color-text-light);
	}
}

.priority-badge {
	display: inline-block;
	padding: 2px 10px;
	border-radius: var(--border-radius-pill);
	font-size: 12px;
	font-weight: 600;
}

.priority {
	&--1 {
		background: var(--color-error);
		color: white;
	}
	&--2 {
		background: var(--color-primary-element);
		color: white;
	}
	&--3 {
		background: var(--color-text-maxcontrast);
		color: white;
	}
}

.text-muted {
	color: var(--color-text-maxcontrast);
	font-style: italic;
}

.text-error {
	color: var(--color-error);
	font-weight: 600;
}

// Responsive: stack sidebar below main on narrow screens
@media (max-width: 768px) {
	.request-detail__body {
		flex-direction: column;
	}

	.request-detail__sidebar {
		width: 100%;
	}
}
</style>
