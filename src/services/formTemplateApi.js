import { api } from './api.js'

export const formTemplateApi = {
	/**
	 * Get all form templates for a process type.
	 * @param {number} procTypeId
	 * @returns {Promise<Array>}
	 */
	async getByProcessType(procTypeId) {
		const response = await api.get(`/api/v1/process-types/${procTypeId}/form-templates`)
		return response.data
	},

	/**
	 * Get a single form template by ID.
	 * @param {number} id
	 * @returns {Promise<Object>}
	 */
	async getById(id) {
		const response = await api.get(`/api/v1/form-templates/${id}`)
		return response.data
	},

	/**
	 * Create a new form template.
	 * @param {number} procTypeId
	 * @param {Object} data
	 * @returns {Promise<Object>}
	 */
	async create(procTypeId, data) {
		const response = await api.post(`/api/v1/process-types/${procTypeId}/form-templates`, data)
		return response.data
	},

	/**
	 * Update a form template.
	 * @param {number} id
	 * @param {Object} data
	 * @returns {Promise<Object>}
	 */
	async update(id, data) {
		const response = await api.put(`/api/v1/form-templates/${id}`, data)
		return response.data
	},

	/**
	 * Delete (soft) a form template.
	 * @param {number} id
	 * @returns {Promise<Object>}
	 */
	async delete(id) {
		const response = await api.delete(`/api/v1/form-templates/${id}`)
		return response.data
	},

	/**
	 * Get all fields for a form template.
	 * @param {number} templateId
	 * @returns {Promise<Array>}
	 */
	async getFields(templateId) {
		const response = await api.get(`/api/v1/form-templates/${templateId}/fields`)
		return response.data
	},

	/**
	 * Create a new field in a form template.
	 * @param {number} templateId
	 * @param {Object} data
	 * @returns {Promise<Object>}
	 */
	async createField(templateId, data) {
		const response = await api.post(`/api/v1/form-templates/${templateId}/fields`, data)
		return response.data
	},

	/**
	 * Update a form field.
	 * @param {number} fieldId
	 * @param {Object} data
	 * @returns {Promise<Object>}
	 */
	async updateField(fieldId, data) {
		const response = await api.put(`/api/v1/form-fields/${fieldId}`, data)
		return response.data
	},

	/**
	 * Delete (soft) a form field.
	 * @param {number} fieldId
	 * @returns {Promise<Object>}
	 */
	async deleteField(fieldId) {
		const response = await api.delete(`/api/v1/form-fields/${fieldId}`)
		return response.data
	},

	/**
	 * Reorder fields within a template.
	 * @param {number} templateId
	 * @param {Array<number>} fieldIds
	 * @returns {Promise<Array>}
	 */
	async reorderFields(templateId, fieldIds) {
		const response = await api.put(`/api/v1/form-templates/${templateId}/fields/reorder`, { fieldIds })
		return response.data
	},
}
