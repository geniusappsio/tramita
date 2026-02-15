import { api } from './api.js'

export const stageApi = {
	async getByProcessType(procTypeId) {
		const response = await api.get(`/process-types/${procTypeId}/stages`)
		return response.data
	},
	async getById(id) {
		const response = await api.get(`/stages/${id}`)
		return response.data
	},
	async create(procTypeId, data) {
		const response = await api.post(`/process-types/${procTypeId}/stages`, data)
		return response.data
	},
	async update(id, data) {
		const response = await api.put(`/stages/${id}`, data)
		return response.data
	},
	async delete(id) {
		const response = await api.delete(`/stages/${id}`)
		return response.data
	},
	async reorder(procTypeId, stageIds) {
		const response = await api.put(`/process-types/${procTypeId}/stages/reorder`, { stageIds })
		return response.data
	},
}
