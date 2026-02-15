import { api } from './api.js'

export const requestApi = {
	async getByProcessType(procTypeId, params = {}) {
		const response = await api.get(`/process-types/${procTypeId}/requests`, { params })
		return response.data
	},
	async getById(id) {
		const response = await api.get(`/requests/${id}`)
		return response.data
	},
	async create(procTypeId, data) {
		const response = await api.post(`/process-types/${procTypeId}/requests`, data)
		return response.data
	},
	async update(id, data) {
		const response = await api.put(`/requests/${id}`, data)
		return response.data
	},
	async delete(id) {
		const response = await api.delete(`/requests/${id}`)
		return response.data
	},
	async move(id, toStageId, comment = null) {
		const response = await api.put(`/requests/${id}/move`, { toStageId, comment })
		return response.data
	},
	async search(query, groupId, limit = 20) {
		const response = await api.get('/requests/search', { params: { query, groupId, limit } })
		return response.data
	},
	async getHistory(id) {
		const response = await api.get(`/requests/${id}/history`)
		return response.data
	},
	async assign(id, userId, role = 'assigned') {
		const response = await api.post(`/requests/${id}/assign`, { userId, role })
		return response.data
	},
	async unassign(id, userId) {
		const response = await api.delete(`/requests/${id}/assign/${userId}`)
		return response.data
	},
	async addLabel(id, labelId) {
		const response = await api.post(`/requests/${id}/labels`, { labelId })
		return response.data
	},
	async removeLabel(id, labelId) {
		const response = await api.delete(`/requests/${id}/labels/${labelId}`)
		return response.data
	},
	async setDeadline(id, dueDate) {
		const response = await api.put(`/requests/${id}/deadline`, { dueDate })
		return response.data
	},
}
