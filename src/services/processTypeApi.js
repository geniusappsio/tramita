import { api } from './api.js'

export const processTypeApi = {
	async getAll() {
		const response = await api.get('/process-types')
		return response.data
	},

	async getById(id) {
		const response = await api.get(`/process-types/${id}`)
		return response.data
	},

	async create(data) {
		const response = await api.post('/process-types', data)
		return response.data
	},

	async update(id, data) {
		const response = await api.put(`/process-types/${id}`, data)
		return response.data
	},

	async delete(id) {
		const response = await api.delete(`/process-types/${id}`)
		return response.data
	},

	async restore(id) {
		const response = await api.put(`/process-types/${id}/restore`)
		return response.data
	},
}
