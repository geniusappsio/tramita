import { api } from './api.js'

export const labelApi = {
	async getAll(params = {}) {
		const response = await api.get('/labels', { params })
		return response.data
	},
	async getById(id) {
		const response = await api.get(`/labels/${id}`)
		return response.data
	},
	async create(data) {
		const response = await api.post('/labels', data)
		return response.data
	},
	async update(id, data) {
		const response = await api.put(`/labels/${id}`, data)
		return response.data
	},
	async delete(id) {
		const response = await api.delete(`/labels/${id}`)
		return response.data
	},
}
