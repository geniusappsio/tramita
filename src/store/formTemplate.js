import { defineStore } from 'pinia'
import { formTemplateApi } from '../services/formTemplateApi.js'

export const useFormTemplateStore = defineStore('formTemplate', {
	state: () => ({
		templates: [],
		currentTemplate: null,
		fields: [],
		loading: false,
		error: null,
	}),

	getters: {
		getByProcessType: (state) => (procTypeId) =>
			state.templates.filter(t => t.procTypeId === procTypeId),
		getById: (state) => (id) =>
			state.templates.find(t => t.id === id),
	},

	actions: {
		async fetchByProcessType(procTypeId) {
			this.loading = true
			this.error = null
			try {
				this.templates = await formTemplateApi.getByProcessType(procTypeId)
			} catch (error) {
				this.error = error.response?.data?.error || 'Erro ao carregar modelos de formulário'
				throw error
			} finally {
				this.loading = false
			}
		},

		async fetchById(id) {
			this.loading = true
			this.error = null
			try {
				this.currentTemplate = await formTemplateApi.getById(id)
				return this.currentTemplate
			} catch (error) {
				this.error = error.response?.data?.error || 'Erro ao carregar modelo de formulário'
				throw error
			} finally {
				this.loading = false
			}
		},

		async create(procTypeId, data) {
			try {
				const created = await formTemplateApi.create(procTypeId, data)
				this.templates.push(created)
				return created
			} catch (error) {
				this.error = error.response?.data?.error || 'Erro ao criar modelo de formulário'
				throw error
			}
		},

		async update(id, data) {
			try {
				const updated = await formTemplateApi.update(id, data)
				const index = this.templates.findIndex(t => t.id === id)
				if (index !== -1) {
					this.templates[index] = updated
				}
				if (this.currentTemplate?.id === id) {
					this.currentTemplate = updated
				}
				return updated
			} catch (error) {
				this.error = error.response?.data?.error || 'Erro ao atualizar modelo de formulário'
				throw error
			}
		},

		async remove(id) {
			try {
				await formTemplateApi.delete(id)
				this.templates = this.templates.filter(t => t.id !== id)
				if (this.currentTemplate?.id === id) {
					this.currentTemplate = null
					this.fields = []
				}
			} catch (error) {
				this.error = error.response?.data?.error || 'Erro ao excluir modelo de formulário'
				throw error
			}
		},

		async fetchFields(templateId) {
			this.loading = true
			this.error = null
			try {
				this.fields = await formTemplateApi.getFields(templateId)
				return this.fields
			} catch (error) {
				this.error = error.response?.data?.error || 'Erro ao carregar campos do formulário'
				throw error
			} finally {
				this.loading = false
			}
		},

		async createField(templateId, data) {
			try {
				const created = await formTemplateApi.createField(templateId, data)
				this.fields.push(created)
				return created
			} catch (error) {
				this.error = error.response?.data?.error || 'Erro ao criar campo'
				throw error
			}
		},

		async updateField(fieldId, data) {
			try {
				const updated = await formTemplateApi.updateField(fieldId, data)
				const index = this.fields.findIndex(f => f.id === fieldId)
				if (index !== -1) {
					this.fields[index] = updated
				}
				return updated
			} catch (error) {
				this.error = error.response?.data?.error || 'Erro ao atualizar campo'
				throw error
			}
		},

		async removeField(fieldId) {
			try {
				await formTemplateApi.deleteField(fieldId)
				this.fields = this.fields.filter(f => f.id !== fieldId)
			} catch (error) {
				this.error = error.response?.data?.error || 'Erro ao excluir campo'
				throw error
			}
		},

		async reorderFields(templateId, fieldIds) {
			try {
				const fields = await formTemplateApi.reorderFields(templateId, fieldIds)
				this.fields = fields
				return fields
			} catch (error) {
				this.error = error.response?.data?.error || 'Erro ao reordenar campos'
				throw error
			}
		},
	},
})
