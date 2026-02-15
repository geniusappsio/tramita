import { defineStore } from 'pinia'
import { processTypeApi } from '../services/processTypeApi.js'

export const useProcessTypeStore = defineStore('processType', {
	state: () => ({
		processTypes: [],
		currentProcessType: null,
		loading: false,
		error: null,
	}),

	getters: {
		activeProcessTypes: (state) => state.processTypes.filter(pt => pt.isActive),
		getById: (state) => (id) => state.processTypes.find(pt => pt.id === id),
	},

	actions: {
		async fetchAll() {
			this.loading = true
			this.error = null
			try {
				this.processTypes = await processTypeApi.getAll()
			} catch (error) {
				this.error = error.response?.data?.error || 'Erro ao carregar tipos de processo'
				throw error
			} finally {
				this.loading = false
			}
		},

		async fetchById(id) {
			this.loading = true
			this.error = null
			try {
				this.currentProcessType = await processTypeApi.getById(id)
				return this.currentProcessType
			} catch (error) {
				this.error = error.response?.data?.error || 'Erro ao carregar tipo de processo'
				throw error
			} finally {
				this.loading = false
			}
		},

		async create(data) {
			try {
				const created = await processTypeApi.create(data)
				this.processTypes.push(created)
				return created
			} catch (error) {
				this.error = error.response?.data?.error || 'Erro ao criar tipo de processo'
				throw error
			}
		},

		async update(id, data) {
			try {
				const updated = await processTypeApi.update(id, data)
				const index = this.processTypes.findIndex(pt => pt.id === id)
				if (index !== -1) {
					this.processTypes[index] = updated
				}
				if (this.currentProcessType?.id === id) {
					this.currentProcessType = updated
				}
				return updated
			} catch (error) {
				this.error = error.response?.data?.error || 'Erro ao atualizar tipo de processo'
				throw error
			}
		},

		async remove(id) {
			try {
				await processTypeApi.delete(id)
				this.processTypes = this.processTypes.filter(pt => pt.id !== id)
			} catch (error) {
				this.error = error.response?.data?.error || 'Erro ao excluir tipo de processo'
				throw error
			}
		},

		async restore(id) {
			try {
				const restored = await processTypeApi.restore(id)
				this.processTypes.push(restored)
				return restored
			} catch (error) {
				this.error = error.response?.data?.error || 'Erro ao restaurar tipo de processo'
				throw error
			}
		},
	},
})
