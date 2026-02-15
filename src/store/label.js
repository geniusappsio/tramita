import { defineStore } from 'pinia'
import { labelApi } from '../services/labelApi.js'

export const useLabelStore = defineStore('label', {
	state: () => ({
		labels: [],
		loading: false,
		error: null,
	}),

	getters: {
		getByGroup: (state) => (groupId) =>
			state.labels.filter(l => l.groupId === groupId),
	},

	actions: {
		async fetchAll(params = {}) {
			this.loading = true
			this.error = null
			try {
				this.labels = await labelApi.getAll(params)
				return this.labels
			} catch (error) {
				this.error = error.response?.data?.error || 'Erro ao carregar labels'
				throw error
			} finally {
				this.loading = false
			}
		},

		async create(data) {
			try {
				const created = await labelApi.create(data)
				this.labels.push(created)
				return created
			} catch (error) {
				this.error = error.response?.data?.error || 'Erro ao criar label'
				throw error
			}
		},

		async update(id, data) {
			try {
				const updated = await labelApi.update(id, data)
				const index = this.labels.findIndex(l => l.id === id)
				if (index !== -1) {
					this.labels[index] = updated
				}
				return updated
			} catch (error) {
				this.error = error.response?.data?.error || 'Erro ao atualizar label'
				throw error
			}
		},

		async remove(id) {
			try {
				await labelApi.delete(id)
				this.labels = this.labels.filter(l => l.id !== id)
			} catch (error) {
				this.error = error.response?.data?.error || 'Erro ao excluir label'
				throw error
			}
		},
	},
})
