<template>
	<div class="stage-manager">
		<!-- Header -->
		<div class="stage-manager__header">
			<h2>{{ processTypeName }} - Etapas</h2>
			<NcButton type="primary" @click="openCreateModal">
				<template #icon>
					<Plus :size="20" />
				</template>
				Nova Etapa
			</NcButton>
		</div>

		<!-- Loading -->
		<NcLoadingIcon v-if="stageStore.loading" :size="44" />

		<!-- Empty state -->
		<NcEmptyContent v-else-if="stages.length === 0"
			name="Nenhuma etapa cadastrada"
			description="Crie a primeira etapa do fluxo de trabalho para este tipo de processo." />

		<!-- Draggable list -->
		<draggable v-else
			v-model="orderedStages"
			handle=".drag-handle"
			@end="onDragEnd">
			<div v-for="stage in orderedStages" :key="stage.id" class="stage-item">
				<div class="drag-handle">
					<DragHorizontalVariant :size="20" />
				</div>
				<div class="stage-item__color"
					:style="{ backgroundColor: stage.color || '#808080' }" />
				<div class="stage-item__info">
					<span class="stage-item__name">{{ stage.name }}</span>
					<div class="stage-item__badges">
						<span v-if="stage.isInitial" class="stage-badge stage-badge--initial">
							Inicial
						</span>
						<span v-if="stage.isFinal" class="stage-badge stage-badge--final">
							Final
						</span>
						<span v-if="stage.slaHours" class="stage-badge stage-badge--sla">
							SLA: {{ stage.slaHours }}h
						</span>
					</div>
					<span v-if="stage.description" class="stage-item__desc">
						{{ stage.description }}
					</span>
				</div>
				<div class="stage-item__actions">
					<NcButton type="tertiary" @click="openEditModal(stage)">
						<template #icon>
							<Pencil :size="20" />
						</template>
					</NcButton>
					<NcButton type="tertiary" @click="confirmDelete(stage)">
						<template #icon>
							<Delete :size="20" />
						</template>
					</NcButton>
				</div>
			</div>
		</draggable>

		<!-- Create/Edit Modal -->
		<NcModal v-if="showModal" @close="closeModal">
			<div class="stage-modal">
				<h2>{{ editingId ? 'Editar' : 'Nova' }} Etapa</h2>
				<form @submit.prevent="saveStage">
					<div class="form-group">
						<label for="stage-name">Nome *</label>
						<input id="stage-name"
							v-model="form.name"
							type="text"
							required
							placeholder="Ex: Analise Inicial">
					</div>
					<div class="form-group">
						<label for="stage-description">Descricao</label>
						<textarea id="stage-description"
							v-model="form.description"
							placeholder="Descricao da etapa"
							rows="3" />
					</div>
					<div class="form-row">
						<div class="form-group form-group--half">
							<label for="stage-color">Cor</label>
							<input id="stage-color"
								v-model="form.color"
								type="color"
								class="color-input">
						</div>
						<div class="form-group form-group--half">
							<label for="stage-sla">SLA (horas)</label>
							<input id="stage-sla"
								v-model.number="form.slaHours"
								type="number"
								min="0"
								placeholder="Ex: 48">
						</div>
					</div>
					<div class="form-row">
						<div class="form-group form-group--half">
							<label class="checkbox-label">
								<input v-model="form.isInitial"
									type="checkbox">
								Etapa Inicial
							</label>
						</div>
						<div class="form-group form-group--half">
							<label class="checkbox-label">
								<input v-model="form.isFinal"
									type="checkbox">
								Etapa Final
							</label>
						</div>
					</div>
					<div class="form-actions">
						<NcButton type="tertiary" @click="closeModal">
							Cancelar
						</NcButton>
						<NcButton type="primary"
							native-type="submit"
							:disabled="saving">
							{{ saving ? 'Salvando...' : 'Salvar' }}
						</NcButton>
					</div>
				</form>
			</div>
		</NcModal>
	</div>
</template>

<script>
import NcButton from '@nextcloud/vue/dist/Components/NcButton.js'
import NcModal from '@nextcloud/vue/dist/Components/NcModal.js'
import NcEmptyContent from '@nextcloud/vue/dist/Components/NcEmptyContent.js'
import NcLoadingIcon from '@nextcloud/vue/dist/Components/NcLoadingIcon.js'
import Plus from 'vue-material-design-icons/Plus.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import Delete from 'vue-material-design-icons/Delete.vue'
import DragHorizontalVariant from 'vue-material-design-icons/DragHorizontalVariant.vue'
import draggable from 'vuedraggable'
import { showSuccess, showError } from '@nextcloud/dialogs'
import { useStageStore } from '../store/stage.js'
import { useProcessTypeStore } from '../store/processType.js'

export default {
	name: 'StageManager',

	components: {
		NcButton,
		NcModal,
		NcEmptyContent,
		NcLoadingIcon,
		Plus,
		Pencil,
		Delete,
		DragHorizontalVariant,
		draggable,
	},

	data() {
		return {
			stageStore: useStageStore(),
			processTypeStore: useProcessTypeStore(),
			showModal: false,
			editingId: null,
			saving: false,
			localOrder: [],
			form: {
				name: '',
				description: '',
				color: '#0082c9',
				isInitial: false,
				isFinal: false,
				slaHours: null,
			},
		}
	},

	computed: {
		processTypeId() {
			return parseInt(this.$route.params.id, 10)
		},

		processTypeName() {
			const pt = this.processTypeStore.getById(this.processTypeId)
			return pt ? pt.name : 'Tipo de Processo'
		},

		stages() {
			return this.stageStore.getByProcessType(this.processTypeId)
		},

		orderedStages: {
			get() {
				if (this.localOrder.length > 0) {
					return this.localOrder
				}
				return [...this.stages].sort((a, b) => a.sortOrder - b.sortOrder)
			},
			set(value) {
				this.localOrder = value
			},
		},
	},

	watch: {
		stages() {
			// Reset local order when stages change from store
			this.localOrder = []
		},
	},

	created() {
		this.stageStore.fetchByProcessType(this.processTypeId)
		// Ensure process type data is loaded for the header
		if (!this.processTypeStore.getById(this.processTypeId)) {
			this.processTypeStore.fetchById(this.processTypeId)
		}
	},

	methods: {
		openCreateModal() {
			this.editingId = null
			this.form = {
				name: '',
				description: '',
				color: '#0082c9',
				isInitial: false,
				isFinal: false,
				slaHours: null,
			}
			this.showModal = true
		},

		openEditModal(stage) {
			this.editingId = stage.id
			this.form = {
				name: stage.name,
				description: stage.description || '',
				color: stage.color || '#0082c9',
				isInitial: stage.isInitial,
				isFinal: stage.isFinal,
				slaHours: stage.slaHours,
			}
			this.showModal = true
		},

		closeModal() {
			this.showModal = false
			this.editingId = null
		},

		async saveStage() {
			this.saving = true
			try {
				const data = {
					name: this.form.name,
					description: this.form.description || null,
					color: this.form.color,
					isInitial: this.form.isInitial,
					isFinal: this.form.isFinal,
					slaHours: this.form.slaHours || null,
				}

				if (this.editingId) {
					await this.stageStore.update(this.editingId, data)
					showSuccess('Etapa atualizada')
				} else {
					await this.stageStore.create(this.processTypeId, data)
					showSuccess('Etapa criada')
				}
				this.closeModal()
			} catch (error) {
				showError(error.response?.data?.error || 'Erro ao salvar etapa')
			} finally {
				this.saving = false
			}
		},

		async confirmDelete(stage) {
			if (confirm(`Tem certeza que deseja excluir "${stage.name}"?`)) {
				try {
					await this.stageStore.remove(stage.id)
					showSuccess('Etapa excluida')
				} catch (error) {
					showError('Erro ao excluir etapa')
				}
			}
		},

		async onDragEnd() {
			const stageIds = this.orderedStages.map(s => s.id)
			try {
				await this.stageStore.reorder(this.processTypeId, stageIds)
				showSuccess('Ordem atualizada')
			} catch (error) {
				showError('Erro ao reordenar etapas')
				// Reset local order on failure
				this.localOrder = []
			}
		},
	},
}
</script>

<style scoped lang="scss">
.stage-manager {
	display: flex;
	flex-direction: column;
	height: 100%;
	padding: 20px;
	max-width: 900px;

	.empty-content {
		flex: 1;
	}

	&__header {
		flex-shrink: 0;
		display: flex;
		justify-content: space-between;
		align-items: center;
		margin-bottom: 20px;

		h2 {
			margin: 0;
		}
	}
}

.stage-item {
	display: flex;
	align-items: center;
	gap: 12px;
	padding: 12px 16px;
	border-radius: var(--border-radius-large);
	background: var(--color-background-dark);
	transition: background 0.2s;
	margin-bottom: 8px;

	&:hover {
		background: var(--color-background-hover);
	}

	&__color {
		width: 12px;
		height: 12px;
		border-radius: 50%;
		flex-shrink: 0;
	}

	&__info {
		flex: 1;
		display: flex;
		flex-direction: column;
		gap: 4px;
		min-width: 0;
	}

	&__name {
		font-weight: bold;
		font-size: 14px;
	}

	&__badges {
		display: flex;
		gap: 6px;
		flex-wrap: wrap;
	}

	&__desc {
		font-size: 13px;
		color: var(--color-text-maxcontrast);
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
	}

	&__actions {
		display: flex;
		gap: 4px;
		flex-shrink: 0;
	}
}

.drag-handle {
	cursor: grab;
	display: flex;
	align-items: center;
	color: var(--color-text-maxcontrast);
	flex-shrink: 0;

	&:active {
		cursor: grabbing;
	}
}

.stage-badge {
	font-size: 11px;
	padding: 2px 8px;
	border-radius: var(--border-radius-pill);
	font-weight: 600;

	&--initial {
		background: var(--color-primary-element);
		color: var(--color-primary-element-text);
	}

	&--final {
		background: var(--color-success);
		color: white;
	}

	&--sla {
		background: var(--color-warning);
		color: white;
	}
}

.stage-modal {
	padding: 20px;
	min-width: 400px;

	h2 {
		margin-top: 0;
		margin-bottom: 16px;
	}
}

.form-group {
	margin-bottom: 12px;

	label {
		display: block;
		font-weight: 600;
		margin-bottom: 4px;
		font-size: 14px;
	}

	input[type="text"],
	input[type="number"],
	textarea {
		width: 100%;
		padding: 8px 10px;
		border: 1px solid var(--color-border-dark);
		border-radius: var(--border-radius);
		font-size: 14px;
		background: var(--color-main-background);
		color: var(--color-main-text);

		&:focus {
			border-color: var(--color-primary-element);
			outline: none;
		}
	}

	&--half {
		flex: 1;
	}
}

.form-row {
	display: flex;
	gap: 12px;
}

.color-input {
	height: 36px;
	padding: 2px;
	cursor: pointer;
}

.checkbox-label {
	display: flex !important;
	align-items: center;
	gap: 8px;
	cursor: pointer;
	font-weight: 400 !important;

	input[type="checkbox"] {
		width: auto;
		margin: 0;
	}
}

.form-actions {
	display: flex;
	justify-content: flex-end;
	gap: 8px;
	margin-top: 16px;
	padding-top: 16px;
	border-top: 1px solid var(--color-border);
}
</style>
