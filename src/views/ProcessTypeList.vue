<template>
	<div class="process-type-list">
		<!-- Header -->
		<div class="process-type-list__header">
			<h2>Tipos de Processo</h2>
			<NcButton type="primary" @click="openCreateModal">
				<template #icon>
					<Plus :size="20" />
				</template>
				Novo Tipo de Processo
			</NcButton>
		</div>

		<!-- Loading -->
		<NcLoadingIcon v-if="store.loading" :size="44" />

		<!-- Empty state -->
		<NcEmptyContent v-else-if="store.processTypes.length === 0"
			name="Nenhum tipo de processo"
			description="Crie seu primeiro tipo de processo para começar a tramitar.">
			<template #icon>
				<FolderIcon :size="20" />
			</template>
		</NcEmptyContent>

		<!-- List -->
		<div v-else class="process-type-list__items">
			<div v-for="pt in store.processTypes" :key="pt.id" class="process-type-item">
				<div class="process-type-item__color" :style="{ backgroundColor: pt.color || '#808080' }" />
				<div class="process-type-item__info">
					<span class="process-type-item__name">{{ pt.name }}</span>
					<span class="process-type-item__prefix">{{ pt.prefix }}</span>
					<span v-if="pt.description" class="process-type-item__desc">{{ pt.description }}</span>
				</div>
				<div class="process-type-item__status">
					<span :class="['status-badge', pt.isActive ? 'status-badge--active' : 'status-badge--inactive']">
						{{ pt.isActive ? 'Ativo' : 'Inativo' }}
					</span>
				</div>
				<div class="process-type-item__actions">
					<NcButton type="tertiary" @click="openEditModal(pt)">
						<template #icon>
							<Pencil :size="20" />
						</template>
					</NcButton>
					<NcButton type="tertiary" @click="confirmDelete(pt)">
						<template #icon>
							<Delete :size="20" />
						</template>
					</NcButton>
				</div>
			</div>
		</div>

		<!-- Create/Edit Modal -->
		<NcModal v-if="showModal" @close="closeModal">
			<div class="process-type-modal">
				<h2>{{ editingId ? 'Editar' : 'Novo' }} Tipo de Processo</h2>
				<form @submit.prevent="saveProcessType">
					<div class="form-group">
						<label for="pt-name">Nome *</label>
						<input id="pt-name" v-model="form.name" type="text" required placeholder="Ex: Memorando">
					</div>
					<div class="form-group">
						<label for="pt-prefix">Prefixo *</label>
						<input id="pt-prefix" v-model="form.prefix" type="text" required
							placeholder="Ex: MEM" maxlength="16">
					</div>
					<div class="form-group">
						<label for="pt-description">Descrição</label>
						<textarea id="pt-description" v-model="form.description"
							placeholder="Descrição do tipo de processo" rows="3" />
					</div>
					<div class="form-row">
						<div class="form-group form-group--half">
							<label for="pt-color">Cor</label>
							<input id="pt-color" v-model="form.color" type="color" class="color-input">
						</div>
						<div class="form-group form-group--half">
							<label for="pt-group">Grupo/Departamento *</label>
							<input id="pt-group" v-model="form.groupId" type="text" required
								placeholder="Nome do grupo Nextcloud">
						</div>
					</div>
					<div class="form-group">
						<label class="checkbox-label">
							<input id="pt-is-external" v-model="form.isExternal" type="checkbox">
							Processo Externo
						</label>
						<span class="form-hint">Permitirá acesso por URL pública sem login (disponível em versão futura)</span>
					</div>
					<div class="form-actions">
						<NcButton type="tertiary" @click="closeModal">Cancelar</NcButton>
						<NcButton type="primary" native-type="submit" :disabled="saving">
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
import FolderIcon from 'vue-material-design-icons/Folder.vue'
import { showSuccess, showError } from '@nextcloud/dialogs'
import { useProcessTypeStore } from '../store/processType.js'

export default {
	name: 'ProcessTypeList',

	components: {
		NcButton,
		NcModal,
		NcEmptyContent,
		NcLoadingIcon,
		Plus,
		Pencil,
		Delete,
		FolderIcon,
	},

	data() {
		return {
			store: useProcessTypeStore(),
			showModal: false,
			editingId: null,
			saving: false,
			form: {
				name: '',
				prefix: '',
				description: '',
				color: '#0082c9',
				groupId: '',
				isExternal: false,
			},
		}
	},

	created() {
		this.store.fetchAll()
	},

	methods: {
		openCreateModal() {
			this.editingId = null
			this.form = { name: '', prefix: '', description: '', color: '#0082c9', groupId: '', isExternal: false }
			this.showModal = true
		},

		openEditModal(pt) {
			this.editingId = pt.id
			this.form = {
				name: pt.name,
				prefix: pt.prefix,
				description: pt.description || '',
				color: pt.color || '#0082c9',
				groupId: pt.groupId,
				isExternal: pt.isExternal || false,
			}
			this.showModal = true
		},

		closeModal() {
			this.showModal = false
			this.editingId = null
		},

		async saveProcessType() {
			this.saving = true
			try {
				if (this.editingId) {
					await this.store.update(this.editingId, this.form)
					showSuccess('Tipo de processo atualizado')
				} else {
					await this.store.create(this.form)
					showSuccess('Tipo de processo criado')
				}
				this.closeModal()
			} catch (error) {
				showError(error.response?.data?.error || 'Erro ao salvar')
			} finally {
				this.saving = false
			}
		},

		async confirmDelete(pt) {
			if (confirm(`Tem certeza que deseja excluir "${pt.name}"?`)) {
				try {
					await this.store.remove(pt.id)
					showSuccess('Tipo de processo excluído')
				} catch (error) {
					showError('Erro ao excluir tipo de processo')
				}
			}
		},
	},
}
</script>

<style scoped lang="scss">
.process-type-list {
	display: flex;
	flex-direction: column;
	height: 100%;
	padding: 20px;
	max-width: 900px;
	margin: 0 auto;
	width: 100%;

	&__header {
		display: flex;
		justify-content: space-between;
		align-items: center;
		margin-bottom: 20px;
		flex-shrink: 0;

		h2 {
			margin: 0;
		}
	}

	&__items {
		display: flex;
		flex-direction: column;
		gap: 8px;
	}

	.empty-content {
		flex: 1;
	}
}

.process-type-item {
	display: flex;
	align-items: center;
	gap: 12px;
	padding: 12px 16px;
	border-radius: var(--border-radius-large);
	background: var(--color-background-dark);
	transition: background 0.2s;

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
		gap: 2px;
		min-width: 0;
	}

	&__name {
		font-weight: bold;
		font-size: 14px;
	}

	&__prefix {
		font-size: 12px;
		color: var(--color-text-maxcontrast);
		font-family: monospace;
		background: var(--color-background-darker);
		padding: 1px 6px;
		border-radius: var(--border-radius-small);
		width: fit-content;
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

.status-badge {
	font-size: 12px;
	padding: 2px 8px;
	border-radius: var(--border-radius-pill);
	font-weight: 600;

	&--active {
		background: var(--color-success);
		color: white;
	}

	&--inactive {
		background: var(--color-text-maxcontrast);
		color: white;
	}
}

.process-type-modal {
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

.form-hint {
	display: block;
	font-size: 12px;
	color: var(--color-text-maxcontrast);
	margin-top: 2px;
}

.color-input {
	height: 36px;
	padding: 2px;
	cursor: pointer;
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
