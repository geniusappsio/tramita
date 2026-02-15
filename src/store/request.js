import { defineStore } from 'pinia'
import { requestApi } from '../services/requestApi.js'

export const useRequestStore = defineStore('request', {
	state: () => ({
		requests: [],
		currentRequest: null,
		loading: false,
		error: null,
	}),

	getters: {
		getByStage: (state) => (stageId) =>
			state.requests.filter(r => r.currentStageId === stageId),
		getByProcessType: (state) => (procTypeId) =>
			state.requests.filter(r => r.procTypeId === procTypeId),
		getById: (state) => (id) =>
			state.requests.find(r => r.id === id),
	},

	actions: {
		async fetchByProcessType(procTypeId, params = {}) {
			this.loading = true
			this.error = null
			try {
				this.requests = await requestApi.getByProcessType(procTypeId, params)
			} catch (error) {
				this.error = error.response?.data?.error || 'Erro ao carregar requisições'
				throw error
			} finally {
				this.loading = false
			}
		},

		async fetchById(id) {
			this.loading = true
			this.error = null
			try {
				this.currentRequest = await requestApi.getById(id)
				return this.currentRequest
			} catch (error) {
				this.error = error.response?.data?.error || 'Erro ao carregar requisição'
				throw error
			} finally {
				this.loading = false
			}
		},

		async create(procTypeId, data) {
			try {
				const created = await requestApi.create(procTypeId, data)
				this.requests.push(created)
				return created
			} catch (error) {
				this.error = error.response?.data?.error || 'Erro ao criar requisição'
				throw error
			}
		},

		async update(id, data) {
			try {
				const updated = await requestApi.update(id, data)
				const index = this.requests.findIndex(r => r.id === id)
				if (index !== -1) {
					this.requests[index] = updated
				}
				if (this.currentRequest?.id === id) {
					this.currentRequest = updated
				}
				return updated
			} catch (error) {
				this.error = error.response?.data?.error || 'Erro ao atualizar requisição'
				throw error
			}
		},

		async remove(id) {
			try {
				await requestApi.delete(id)
				this.requests = this.requests.filter(r => r.id !== id)
				if (this.currentRequest?.id === id) {
					this.currentRequest = null
				}
			} catch (error) {
				this.error = error.response?.data?.error || 'Erro ao excluir requisição'
				throw error
			}
		},

		async move(id, toStageId, comment = null) {
			try {
				const updated = await requestApi.move(id, toStageId, comment)
				const index = this.requests.findIndex(r => r.id === id)
				if (index !== -1) {
					this.requests[index] = updated
				}
				if (this.currentRequest?.id === id) {
					this.currentRequest = updated
				}
				return updated
			} catch (error) {
				this.error = error.response?.data?.error || 'Erro ao mover requisição'
				throw error
			}
		},

		async search(query, groupId) {
			this.loading = true
			this.error = null
			try {
				const results = await requestApi.search(query, groupId)
				return results
			} catch (error) {
				this.error = error.response?.data?.error || 'Erro ao buscar requisições'
				throw error
			} finally {
				this.loading = false
			}
		},
	},
})
