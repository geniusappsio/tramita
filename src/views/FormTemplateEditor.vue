<template>
	<div class="tramita-form-editor">
		<div class="form-editor-layout">
			<!-- Left sidebar: template list -->
			<div class="form-editor-sidebar">
				<div class="sidebar-header">
					<h3>Modelos de Formulário</h3>
					<NcButton type="primary"
						:aria-label="'Adicionar modelo'"
						@click="showAddTemplate = true">
						<template #icon>
							<Plus :size="20" />
						</template>
						Novo
					</NcButton>
				</div>

				<NcLoadingIcon v-if="store.loading && templates.length === 0" />

				<NcEmptyContent v-else-if="templates.length === 0"
					name="Nenhum modelo"
					description="Crie o primeiro modelo de formulário para este tipo de processo.">
					<template #icon>
						<TextBoxOutline :size="48" />
					</template>
				</NcEmptyContent>

				<ul v-else class="template-list">
					<li v-for="tpl in templates"
						:key="tpl.id"
						:class="['template-item', { active: selectedTemplateId === tpl.id }]"
						@click="selectTemplate(tpl)">
						<span class="template-name">{{ tpl.name }}</span>
						<span v-if="tpl.isRequired" class="required-badge">Obrigatório</span>
						<div class="template-actions">
							<NcButton type="tertiary"
								:aria-label="'Editar modelo'"
								@click.stop="editTemplate(tpl)">
								<template #icon>
									<Pencil :size="16" />
								</template>
							</NcButton>
							<NcButton type="tertiary"
								:aria-label="'Excluir modelo'"
								@click.stop="confirmDeleteTemplate(tpl)">
								<template #icon>
									<Delete :size="16" />
								</template>
							</NcButton>
						</div>
					</li>
				</ul>
			</div>

			<!-- Main area: fields editor -->
			<div class="form-editor-main">
				<template v-if="selectedTemplateId">
					<div class="main-header">
						<h3>{{ currentTemplateName }}</h3>
						<NcButton type="primary"
							:aria-label="'Adicionar campo'"
							@click="openFieldModal()">
							<template #icon>
								<Plus :size="20" />
							</template>
							Adicionar Campo
						</NcButton>
					</div>

					<NcLoadingIcon v-if="store.loading && fields.length === 0" />

					<NcEmptyContent v-else-if="fields.length === 0"
						name="Nenhum campo"
						description="Adicione campos ao modelo de formulário.">
						<template #icon>
							<TextBoxOutline :size="48" />
						</template>
					</NcEmptyContent>

					<draggable v-else
						v-model="sortableFields"
						handle=".drag-handle"
						@end="onFieldReorder">
						<div v-for="field in sortableFields"
							:key="field.id"
							class="field-card">
							<div class="drag-handle">
								<DragHorizontalVariant :size="20" />
							</div>
							<div class="field-info">
								<div class="field-header">
									<component :is="getFieldIcon(field.fieldType)" :size="18" class="field-type-icon" />
									<span class="field-type-badge">{{ field.fieldType }}</span>
									<span class="field-label">{{ field.label }}</span>
									<span v-if="field.isRequired" class="required-indicator">*</span>
								</div>
								<span class="field-name">{{ field.name }}</span>
							</div>
							<div class="field-width-badge">{{ getWidthLabel(field.width) }}</div>
							<div class="field-actions">
								<NcButton type="tertiary"
									:aria-label="'Editar campo'"
									@click="openFieldModal(field)">
									<template #icon>
										<Pencil :size="16" />
									</template>
								</NcButton>
								<NcButton type="tertiary"
									:aria-label="'Excluir campo'"
									@click="confirmDeleteField(field)">
									<template #icon>
										<Delete :size="16" />
									</template>
								</NcButton>
							</div>
						</div>
					</draggable>
				</template>

				<NcEmptyContent v-else
					name="Selecione um modelo"
					description="Escolha um modelo de formulário na lista ao lado para editar seus campos.">
					<template #icon>
						<TextBoxOutline :size="48" />
					</template>
				</NcEmptyContent>
			</div>
		</div>

		<!-- Add/Edit Template Modal -->
		<NcModal v-if="showAddTemplate || editingTemplate"
			:name="editingTemplate ? 'Editar Modelo' : 'Novo Modelo'"
			@close="closeTemplateModal">
			<div class="modal-content">
				<h3>{{ editingTemplate ? 'Editar Modelo' : 'Novo Modelo de Formulário' }}</h3>
				<div class="form-group">
					<label for="tpl-name">Nome *</label>
					<input id="tpl-name"
						v-model="templateForm.name"
						type="text"
						placeholder="Nome do modelo"
						required>
				</div>
				<div class="form-group">
					<label for="tpl-description">Descrição</label>
					<textarea id="tpl-description"
						v-model="templateForm.description"
						placeholder="Descrição do modelo"
						rows="3" />
				</div>
				<div class="form-group">
					<label class="checkbox-label">
						<input v-model="templateForm.isRequired"
							type="checkbox">
						Obrigatório
					</label>
				</div>
				<div class="modal-actions">
					<NcButton type="tertiary" @click="closeTemplateModal">
						Cancelar
					</NcButton>
					<NcButton type="primary" @click="saveTemplate">
						{{ editingTemplate ? 'Salvar' : 'Criar' }}
					</NcButton>
				</div>
			</div>
		</NcModal>

		<!-- Add/Edit Field Modal -->
		<NcModal v-if="showFieldModal"
			name="Campo"
			size="large"
			@close="closeFieldModal">
			<div class="modal-content">
				<h3>{{ editingField ? 'Editar Campo' : 'Novo Campo' }}</h3>

				<div class="form-row">
					<div class="form-group form-group--half">
						<label for="field-label">Rótulo *</label>
						<input id="field-label"
							v-model="fieldForm.label"
							type="text"
							placeholder="Rótulo do campo"
							required
							@input="onLabelInput">
					</div>
					<div class="form-group form-group--half">
						<label for="field-name">Nome (identificador) *</label>
						<input id="field-name"
							v-model="fieldForm.name"
							type="text"
							placeholder="nome_do_campo"
							class="monospace"
							required>
					</div>
				</div>

				<div class="form-row">
					<div class="form-group form-group--half">
						<label for="field-type">Tipo *</label>
						<NcSelect v-model="fieldForm.fieldType"
							:options="fieldTypeOptions"
							label="label"
							:reduce="opt => opt.value"
							:clearable="false"
							input-id="field-type"
							placeholder="Selecione o tipo..." />
					</div>
					<div class="form-group form-group--half">
						<label for="field-width">Largura</label>
						<NcSelect v-model="fieldForm.width"
							:options="widthOptions"
							label="label"
							:reduce="opt => opt.value"
							:clearable="false"
							input-id="field-width" />
					</div>
				</div>

				<div class="form-group">
					<label for="field-placeholder">Placeholder</label>
					<input id="field-placeholder"
						v-model="fieldForm.placeholder"
						type="text"
						placeholder="Texto de exemplo">
				</div>

				<div class="form-group">
					<label for="field-help">Texto de ajuda</label>
					<input id="field-help"
						v-model="fieldForm.helpText"
						type="text"
						placeholder="Texto de ajuda exibido abaixo do campo">
				</div>

				<div class="form-group">
					<label class="checkbox-label">
						<input v-model="fieldForm.isRequired"
							type="checkbox">
						Obrigatório
					</label>
				</div>

				<!-- Options editor for select/radio -->
				<div v-if="fieldForm.fieldType === 'select' || fieldForm.fieldType === 'radio'"
					class="options-editor">
					<label>Opções</label>
					<div v-for="(opt, idx) in fieldForm.options"
						:key="idx"
						class="option-row">
						<input v-model="opt.value"
							type="text"
							placeholder="Valor"
							class="option-input">
						<input v-model="opt.label"
							type="text"
							placeholder="Rótulo"
							class="option-input">
						<NcButton type="tertiary"
							:aria-label="'Remover opção'"
							@click="removeOption(idx)">
							<template #icon>
								<Delete :size="16" />
							</template>
						</NcButton>
					</div>
					<NcButton type="secondary" @click="addOption">
						<template #icon>
							<Plus :size="16" />
						</template>
						Adicionar opção
					</NcButton>
				</div>

				<div class="modal-actions">
					<NcButton type="tertiary" @click="closeFieldModal">
						Cancelar
					</NcButton>
					<NcButton type="primary" @click="saveField">
						{{ editingField ? 'Salvar' : 'Criar' }}
					</NcButton>
				</div>
			</div>
		</NcModal>
	</div>
</template>

<script>
import { NcButton, NcModal, NcEmptyContent, NcLoadingIcon, NcSelect } from '@nextcloud/vue'
import { showSuccess, showError } from '@nextcloud/dialogs'
import draggable from 'vuedraggable'

import Plus from 'vue-material-design-icons/Plus.vue'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import Delete from 'vue-material-design-icons/Delete.vue'
import DragHorizontalVariant from 'vue-material-design-icons/DragHorizontalVariant.vue'
import TextBoxOutline from 'vue-material-design-icons/TextBoxOutline.vue'
import Numeric from 'vue-material-design-icons/Numeric.vue'
import CalendarRange from 'vue-material-design-icons/CalendarRange.vue'
import FormatListBulleted from 'vue-material-design-icons/FormatListBulleted.vue'
import TextLong from 'vue-material-design-icons/TextLong.vue'
import FileOutline from 'vue-material-design-icons/FileOutline.vue'
import CheckboxMarkedOutline from 'vue-material-design-icons/CheckboxMarkedOutline.vue'
import RadioboxMarked from 'vue-material-design-icons/RadioboxMarked.vue'
import EmailOutline from 'vue-material-design-icons/EmailOutline.vue'
import Phone from 'vue-material-design-icons/Phone.vue'
import CurrencyUsd from 'vue-material-design-icons/CurrencyUsd.vue'
import AccountOutline from 'vue-material-design-icons/AccountOutline.vue'
import CardAccountDetailsOutline from 'vue-material-design-icons/CardAccountDetailsOutline.vue'

import { useFormTemplateStore } from '../store/formTemplate.js'

export default {
	name: 'FormTemplateEditor',

	components: {
		NcButton,
		NcModal,
		NcEmptyContent,
		NcLoadingIcon,
		NcSelect,
		draggable,
		Plus,
		Pencil,
		Delete,
		DragHorizontalVariant,
		TextBoxOutline,
		Numeric,
		CalendarRange,
		FormatListBulleted,
		TextLong,
		FileOutline,
		CheckboxMarkedOutline,
		RadioboxMarked,
		EmailOutline,
		Phone,
		CurrencyUsd,
		AccountOutline,
		CardAccountDetailsOutline,
	},

	data() {
		return {
			processTypeId: null,
			selectedTemplateId: null,

			// Template modal
			showAddTemplate: false,
			editingTemplate: null,
			templateForm: {
				name: '',
				description: '',
				isRequired: false,
			},

			// Field modal
			showFieldModal: false,
			editingField: null,
			fieldForm: {
				name: '',
				label: '',
				fieldType: 'text',
				placeholder: '',
				helpText: '',
				isRequired: false,
				width: 'full',
				options: [],
			},

			// Field type options for NcSelect
			fieldTypeOptions: [
				{ value: 'text', label: 'Texto' },
				{ value: 'number', label: 'Número' },
				{ value: 'date', label: 'Data' },
				{ value: 'select', label: 'Lista de seleção' },
				{ value: 'textarea', label: 'Área de texto' },
				{ value: 'file', label: 'Arquivo' },
				{ value: 'checkbox', label: 'Caixa de seleção' },
				{ value: 'radio', label: 'Botão de rádio' },
				{ value: 'email', label: 'E-mail' },
				{ value: 'cpf', label: 'CPF' },
				{ value: 'cnpj', label: 'CNPJ' },
				{ value: 'phone', label: 'Telefone' },
				{ value: 'currency', label: 'Moeda' },
				{ value: 'user_select', label: 'Seletor de usuário' },
			],

			// Width options for NcSelect
			widthOptions: [
				{ value: 'full', label: 'Inteira' },
				{ value: 'half', label: 'Meia' },
				{ value: 'third', label: 'Um terço' },
			],
		}
	},

	computed: {
		store() {
			return useFormTemplateStore()
		},

		templates() {
			return this.store.templates
		},

		fields() {
			return this.store.fields
		},

		sortableFields: {
			get() {
				return [...this.store.fields]
			},
			set(value) {
				this.store.fields = value
			},
		},

		currentTemplateName() {
			const tpl = this.templates.find(t => t.id === this.selectedTemplateId)
			return tpl ? tpl.name : ''
		},
	},

	created() {
		this.processTypeId = parseInt(this.$route.params.id, 10)
		this.loadTemplates()
	},

	methods: {
		async loadTemplates() {
			try {
				await this.store.fetchByProcessType(this.processTypeId)
			} catch (e) {
				showError('Erro ao carregar modelos de formulário')
			}
		},

		async selectTemplate(tpl) {
			this.selectedTemplateId = tpl.id
			try {
				await this.store.fetchFields(tpl.id)
			} catch (e) {
				showError('Erro ao carregar campos')
			}
		},

		// Template CRUD
		editTemplate(tpl) {
			this.editingTemplate = tpl
			this.templateForm = {
				name: tpl.name,
				description: tpl.description || '',
				isRequired: tpl.isRequired,
			}
		},

		closeTemplateModal() {
			this.showAddTemplate = false
			this.editingTemplate = null
			this.templateForm = { name: '', description: '', isRequired: false }
		},

		async saveTemplate() {
			if (!this.templateForm.name.trim()) {
				showError('O nome é obrigatório')
				return
			}

			try {
				if (this.editingTemplate) {
					await this.store.update(this.editingTemplate.id, {
						name: this.templateForm.name,
						description: this.templateForm.description,
						isRequired: this.templateForm.isRequired,
					})
					showSuccess('Modelo atualizado')
				} else {
					const created = await this.store.create(this.processTypeId, {
						name: this.templateForm.name,
						description: this.templateForm.description,
						isRequired: this.templateForm.isRequired,
					})
					this.selectTemplate(created)
					showSuccess('Modelo criado')
				}
				this.closeTemplateModal()
			} catch (e) {
				showError(e.response?.data?.error || 'Erro ao salvar modelo')
			}
		},

		async confirmDeleteTemplate(tpl) {
			if (!confirm('Tem certeza que deseja excluir o modelo "' + tpl.name + '"?')) {
				return
			}
			try {
				await this.store.remove(tpl.id)
				if (this.selectedTemplateId === tpl.id) {
					this.selectedTemplateId = null
				}
				showSuccess('Modelo excluído')
			} catch (e) {
				showError('Erro ao excluir modelo')
			}
		},

		// Field CRUD
		openFieldModal(field) {
			if (field) {
				this.editingField = field
				this.fieldForm = {
					name: field.name,
					label: field.label,
					fieldType: field.fieldType,
					placeholder: field.placeholder || '',
					helpText: field.helpText || '',
					isRequired: field.isRequired,
					width: field.width || 'full',
					options: field.options ? [...field.options] : [],
				}
			} else {
				this.editingField = null
				this.fieldForm = {
					name: '',
					label: '',
					fieldType: 'text',
					placeholder: '',
					helpText: '',
					isRequired: false,
					width: 'full',
					options: [],
				}
			}
			this.showFieldModal = true
		},

		closeFieldModal() {
			this.showFieldModal = false
			this.editingField = null
		},

		onLabelInput() {
			if (!this.editingField) {
				this.fieldForm.name = this.toSnakeCase(this.fieldForm.label)
			}
		},

		toSnakeCase(str) {
			return str
				.normalize('NFD')
				.replace(/[\u0300-\u036f]/g, '')
				.toLowerCase()
				.replace(/[^a-z0-9]+/g, '_')
				.replace(/^_|_$/g, '')
		},

		addOption() {
			this.fieldForm.options.push({ value: '', label: '' })
		},

		removeOption(idx) {
			this.fieldForm.options.splice(idx, 1)
		},

		async saveField() {
			if (!this.fieldForm.name.trim() || !this.fieldForm.label.trim()) {
				showError('Nome e rótulo são obrigatórios')
				return
			}

			const data = {
				name: this.fieldForm.name,
				label: this.fieldForm.label,
				fieldType: this.fieldForm.fieldType,
				placeholder: this.fieldForm.placeholder || null,
				helpText: this.fieldForm.helpText || null,
				isRequired: this.fieldForm.isRequired,
				width: this.fieldForm.width,
			}

			if (this.fieldForm.fieldType === 'select' || this.fieldForm.fieldType === 'radio') {
				data.options = this.fieldForm.options.filter(o => o.value.trim() !== '')
			}

			try {
				if (this.editingField) {
					await this.store.updateField(this.editingField.id, data)
					showSuccess('Campo atualizado')
				} else {
					await this.store.createField(this.selectedTemplateId, data)
					showSuccess('Campo criado')
				}
				this.closeFieldModal()
			} catch (e) {
				showError(e.response?.data?.error || 'Erro ao salvar campo')
			}
		},

		async confirmDeleteField(field) {
			if (!confirm('Tem certeza que deseja excluir o campo "' + field.label + '"?')) {
				return
			}
			try {
				await this.store.removeField(field.id)
				showSuccess('Campo excluído')
			} catch (e) {
				showError('Erro ao excluir campo')
			}
		},

		async onFieldReorder() {
			const fieldIds = this.sortableFields.map(f => f.id)
			try {
				await this.store.reorderFields(this.selectedTemplateId, fieldIds)
			} catch (e) {
				showError('Erro ao reordenar campos')
			}
		},

		getFieldIcon(fieldType) {
			const iconMap = {
				text: 'TextBoxOutline',
				number: 'Numeric',
				date: 'CalendarRange',
				select: 'FormatListBulleted',
				textarea: 'TextLong',
				file: 'FileOutline',
				checkbox: 'CheckboxMarkedOutline',
				radio: 'RadioboxMarked',
				email: 'EmailOutline',
				cpf: 'CardAccountDetailsOutline',
				cnpj: 'CardAccountDetailsOutline',
				phone: 'Phone',
				currency: 'CurrencyUsd',
				user_select: 'AccountOutline',
			}
			return iconMap[fieldType] || 'TextBoxOutline'
		},

		getWidthLabel(width) {
			const labels = {
				full: 'Inteira',
				half: 'Meia',
				third: '1/3',
			}
			return labels[width] || width
		},
	},
}
</script>

<style lang="scss" scoped>
.tramita-form-editor {
	height: 100%;
	padding: 16px;
}

.form-editor-layout {
	display: flex;
	gap: 24px;
	height: calc(100vh - 100px);
}

.form-editor-sidebar {
	width: 300px;
	min-width: 260px;
	border-right: 1px solid var(--color-border);
	padding-right: 16px;
	overflow-y: auto;

	.sidebar-header {
		display: flex;
		justify-content: space-between;
		align-items: center;
		margin-bottom: 16px;

		h3 {
			margin: 0;
			font-size: 16px;
		}
	}
}

.template-list {
	list-style: none;
	padding: 0;
	margin: 0;
}

.template-item {
	display: flex;
	align-items: center;
	flex-wrap: wrap;
	gap: 8px;
	padding: 10px 12px;
	border-radius: var(--border-radius-large);
	cursor: pointer;
	transition: background-color 0.15s;

	&:hover {
		background-color: var(--color-background-hover);
	}

	&.active {
		background-color: var(--color-primary-element-light);
	}

	.template-name {
		flex: 1;
		font-weight: 500;
	}

	.required-badge {
		font-size: 11px;
		background-color: var(--color-warning);
		color: white;
		padding: 2px 6px;
		border-radius: 10px;
	}

	.template-actions {
		display: flex;
		gap: 0;
	}
}

.form-editor-main {
	flex: 1;
	overflow-y: auto;

	.main-header {
		display: flex;
		justify-content: space-between;
		align-items: center;
		margin-bottom: 16px;

		h3 {
			margin: 0;
			font-size: 18px;
		}
	}
}

.field-card {
	display: flex;
	align-items: center;
	gap: 12px;
	padding: 12px 16px;
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-large);
	margin-bottom: 8px;
	background: var(--color-main-background);
	transition: box-shadow 0.15s;

	&:hover {
		box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
	}

	.drag-handle {
		cursor: grab;
		color: var(--color-text-maxcontrast);

		&:active {
			cursor: grabbing;
		}
	}

	.field-info {
		flex: 1;
		min-width: 0;

		.field-header {
			display: flex;
			align-items: center;
			gap: 6px;
			margin-bottom: 2px;
		}

		.field-type-icon {
			color: var(--color-primary-element);
		}

		.field-type-badge {
			font-size: 11px;
			background-color: var(--color-background-dark);
			padding: 1px 6px;
			border-radius: 8px;
			color: var(--color-text-maxcontrast);
		}

		.field-label {
			font-weight: 500;
		}

		.required-indicator {
			color: var(--color-error);
			font-weight: bold;
		}

		.field-name {
			font-family: monospace;
			font-size: 12px;
			color: var(--color-text-maxcontrast);
		}
	}

	.field-width-badge {
		font-size: 11px;
		background-color: var(--color-background-dark);
		padding: 2px 8px;
		border-radius: 8px;
		color: var(--color-text-maxcontrast);
		white-space: nowrap;
	}

	.field-actions {
		display: flex;
		gap: 0;
	}
}

// Modal styles
.modal-content {
	padding: 24px;

	h3 {
		margin-top: 0;
		margin-bottom: 16px;
	}
}

.form-group {
	margin-bottom: 16px;

	label {
		display: block;
		font-weight: 500;
		margin-bottom: 4px;
	}

	input[type='text'],
	input[type='number'],
	textarea {
		width: 100%;
		padding: 8px 12px;
		border: 1px solid var(--color-border);
		border-radius: var(--border-radius);
		background: var(--color-main-background);
		font-size: 14px;

		&:focus {
			border-color: var(--color-primary-element);
			outline: none;
		}
	}

	.monospace {
		font-family: monospace;
	}

	&--half {
		flex: 1;
		min-width: 0;
	}
}

.form-row {
	display: flex;
	gap: 16px;
}

.checkbox-label {
	display: flex !important;
	align-items: center;
	gap: 8px;
	cursor: pointer;
	font-weight: normal !important;

	input[type='checkbox'] {
		width: auto;
	}
}

.options-editor {
	margin-bottom: 16px;
	padding: 12px;
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-large);
	background: var(--color-background-dark);

	> label {
		display: block;
		font-weight: 500;
		margin-bottom: 8px;
	}

	.option-row {
		display: flex;
		gap: 8px;
		margin-bottom: 8px;
		align-items: center;

		.option-input {
			flex: 1;
			padding: 6px 10px;
			border: 1px solid var(--color-border);
			border-radius: var(--border-radius);
			background: var(--color-main-background);
			font-size: 13px;
		}
	}
}

.modal-actions {
	display: flex;
	justify-content: flex-end;
	gap: 8px;
	margin-top: 20px;
	padding-top: 16px;
	border-top: 1px solid var(--color-border);
}
</style>
