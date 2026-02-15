<template>
	<div class="request-form">
		<div class="request-form__header">
			<h2>{{ isEditing ? 'Editar Requisição' : 'Nova Requisição' }}</h2>
			<p v-if="processTypeName" class="request-form__subtitle">{{ processTypeName }}</p>
		</div>

		<NcLoadingIcon v-if="loadingData" class="request-form__loading" />

		<form v-else @submit.prevent="submitForm">
			<div class="form-section">
				<h3>Informações Básicas</h3>
				<div class="form-group">
					<label for="req-title">Título *</label>
					<input id="req-title" v-model="form.title" type="text" required
						placeholder="Título da requisição">
				</div>
				<div class="form-group">
					<label for="req-description">Descrição</label>
					<textarea id="req-description" v-model="form.description" rows="4"
						placeholder="Descreva a requisição..." />
				</div>
				<div class="form-row">
					<div class="form-group form-group--third">
						<label for="req-priority">Prioridade</label>
						<select id="req-priority" v-model.number="form.priority">
							<option :value="1">Urgente</option>
							<option :value="2" selected>Normal</option>
							<option :value="3">Baixa</option>
						</select>
					</div>
					<div class="form-group form-group--third">
						<label for="req-due">Prazo</label>
						<input id="req-due" v-model="form.dueDate" type="date">
					</div>
					<div class="form-group form-group--third">
						<label class="checkbox-label">
							<input v-model="form.isConfidential" type="checkbox">
							Confidencial
						</label>
					</div>
				</div>
			</div>

			<!-- Dynamic form fields from templates -->
			<div v-for="template in formTemplates" :key="template.id" class="form-section">
				<h3>{{ template.name }}</h3>
				<p v-if="template.description" class="form-section__desc">{{ template.description }}</p>
				<DynamicForm
					:fields="templateFields[template.id] || []"
					:values="formValues[template.id] || {}"
					:errors="{}"
					@update:values="updateTemplateValues(template.id, $event)" />
			</div>

			<div class="form-actions">
				<NcButton type="tertiary" @click="$router.back()">Cancelar</NcButton>
				<NcButton type="primary" native-type="submit" :disabled="saving">
					{{ saving ? 'Salvando...' : (isEditing ? 'Salvar Alterações' : 'Criar Requisição') }}
				</NcButton>
			</div>
		</form>
	</div>
</template>

<script>
import NcButton from '@nextcloud/vue/dist/Components/NcButton.js'
import NcLoadingIcon from '@nextcloud/vue/dist/Components/NcLoadingIcon.js'
import { showSuccess, showError } from '@nextcloud/dialogs'
import DynamicForm from '../components/forms/DynamicForm.vue'
import { useRequestStore } from '../store/request.js'
import { useFormTemplateStore } from '../store/formTemplate.js'
import { useProcessTypeStore } from '../store/processType.js'

export default {
	name: 'RequestForm',

	components: {
		NcButton,
		NcLoadingIcon,
		DynamicForm,
	},

	data() {
		return {
			form: {
				title: '',
				description: '',
				priority: 2,
				dueDate: null,
				isConfidential: false,
			},
			formValues: {},
			formTemplates: [],
			templateFields: {},
			processTypeName: '',
			loadingData: true,
			saving: false,
		}
	},

	computed: {
		processTypeId() {
			return Number(this.$route.params.processTypeId || this.$route.params.id)
		},
		requestId() {
			return this.$route.params.id && this.$route.name === 'editRequest'
				? Number(this.$route.params.id)
				: null
		},
		isEditing() {
			return this.requestId !== null
		},
	},

	created() {
		this.loadInitialData()
	},

	methods: {
		async loadInitialData() {
			this.loadingData = true
			try {
				const processTypeStore = useProcessTypeStore()
				const formTemplateStore = useFormTemplateStore()
				const requestStore = useRequestStore()

				if (this.isEditing) {
					// Editing: load the existing request
					const request = await requestStore.fetchById(this.requestId)
					this.form.title = request.title || ''
					this.form.description = request.description || ''
					this.form.priority = request.priority || 2
					this.form.dueDate = request.dueDate ? request.dueDate.substring(0, 10) : null
					this.form.isConfidential = request.isConfidential || false

					// Load process type info
					const pt = await processTypeStore.fetchById(request.procTypeId)
					this.processTypeName = pt.name
				} else {
					// Creating: load process type info
					const pt = await processTypeStore.fetchById(this.processTypeId)
					this.processTypeName = pt.name
				}

				// Load form templates and their fields
				const targetProcTypeId = this.isEditing
					? (await requestStore.fetchById(this.requestId)).procTypeId
					: this.processTypeId

				await formTemplateStore.fetchByProcessType(targetProcTypeId)
				this.formTemplates = formTemplateStore.templates

				// Load fields for each template
				for (const template of this.formTemplates) {
					const fields = await formTemplateStore.fetchFields(template.id)
					this.$set(this.templateFields, template.id, fields)
					if (!this.formValues[template.id]) {
						this.$set(this.formValues, template.id, {})
					}
				}
			} catch (error) {
				showError('Erro ao carregar dados do formulário')
				console.error('RequestForm loadInitialData error:', error)
			} finally {
				this.loadingData = false
			}
		},

		updateTemplateValues(templateId, values) {
			this.$set(this.formValues, templateId, values)
		},

		async submitForm() {
			if (!this.form.title.trim()) {
				showError('O título é obrigatório')
				return
			}

			this.saving = true
			try {
				const requestStore = useRequestStore()

				if (this.isEditing) {
					await requestStore.update(this.requestId, {
						title: this.form.title,
						description: this.form.description,
						priority: this.form.priority,
						dueDate: this.form.dueDate,
						isConfidential: this.form.isConfidential,
					})
					showSuccess('Requisição atualizada com sucesso')
				} else {
					await requestStore.create(this.processTypeId, {
						title: this.form.title,
						description: this.form.description,
						priority: this.form.priority,
						dueDate: this.form.dueDate,
						isConfidential: this.form.isConfidential,
						groupId: this.getGroupId(),
					})
					showSuccess('Requisição criada com sucesso')
				}

				this.$router.push({ name: 'kanbanBoard', params: { processTypeId: this.getTargetProcessTypeId() } })
			} catch (error) {
				const msg = error.response?.data?.error || 'Erro ao salvar requisição'
				showError(msg)
				console.error('RequestForm submit error:', error)
			} finally {
				this.saving = false
			}
		},

		getGroupId() {
			const processTypeStore = useProcessTypeStore()
			return processTypeStore.currentProcessType?.groupId || ''
		},

		getTargetProcessTypeId() {
			if (this.isEditing) {
				const requestStore = useRequestStore()
				return requestStore.currentRequest?.procTypeId || this.processTypeId
			}
			return this.processTypeId
		},
	},
}
</script>

<style lang="scss" scoped>
.request-form {
	max-width: 800px;
	margin: 0 auto;
	padding: 20px;

	&__header {
		margin-bottom: 24px;

		h2 {
			margin: 0 0 4px;
			font-size: 22px;
			font-weight: 700;
		}
	}

	&__subtitle {
		margin: 0;
		font-size: 14px;
		color: var(--color-text-maxcontrast);
	}

	&__loading {
		display: flex;
		justify-content: center;
		padding: 40px 0;
	}
}

.form-section {
	background: var(--color-main-background);
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-large);
	padding: 20px;
	margin-bottom: 16px;

	h3 {
		margin: 0 0 16px;
		font-size: 16px;
		font-weight: 600;
	}

	&__desc {
		margin: -8px 0 16px;
		font-size: 13px;
		color: var(--color-text-maxcontrast);
	}
}

.form-group {
	margin-bottom: 16px;

	label {
		display: block;
		font-weight: 500;
		margin-bottom: 4px;
		font-size: 14px;
	}

	input[type='text'],
	input[type='date'],
	textarea,
	select {
		width: 100%;
		padding: 8px 12px;
		border: 1px solid var(--color-border);
		border-radius: var(--border-radius);
		background: var(--color-main-background);
		font-size: 14px;
		color: var(--color-main-text);
		transition: border-color 0.15s;

		&:focus {
			border-color: var(--color-primary-element);
			outline: none;
		}
	}

	textarea {
		resize: vertical;
		min-height: 80px;
	}

	select {
		appearance: auto;
	}

	&--third {
		flex: 1;
		min-width: 0;
	}
}

.form-row {
	display: flex;
	gap: 16px;
	align-items: flex-start;
}

.checkbox-label {
	display: flex !important;
	align-items: center;
	gap: 8px;
	cursor: pointer;
	padding-top: 24px;

	input[type='checkbox'] {
		width: auto;
		margin: 0;
	}
}

.form-actions {
	display: flex;
	justify-content: flex-end;
	gap: 8px;
	margin-top: 24px;
	padding-top: 16px;
	border-top: 1px solid var(--color-border);
}

// Responsive: stack form-row on narrow screens
@media (max-width: 600px) {
	.form-row {
		flex-direction: column;
	}

	.form-group--third {
		width: 100%;
	}

	.checkbox-label {
		padding-top: 0;
	}
}
</style>
