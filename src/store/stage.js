import { defineStore } from 'pinia'
import { stageApi } from '../services/stageApi.js'

export const useStageStore = defineStore('stage', {
	state: () => ({
		stages: [],
		loading: false,
		error: null,
	}),

	getters: {
		getByProcessType: (state) => (procTypeId) =>
			state.stages.filter(s => s.procTypeId === procTypeId),
		getById: (state) => (id) =>
			state.stages.find(s => s.id === id),
	},

	actions: {
		async fetchByProcessType(procTypeId) {
			this.loading = true
			this.error = null
			try {
				const stages = await stageApi.getByProcessType(procTypeId)
				// Replace stages for this process type while keeping others
				this.stages = [
					...this.stages.filter(s => s.procTypeId !== procTypeId),
					...stages,
				]
				return stages
			} catch (error) {
				this.error = error.response?.data?.error || 'Erro ao carregar etapas'
				throw error
			} finally {
				this.loading = false
			}
		},

		async create(procTypeId, data) {
			try {
				const created = await stageApi.create(procTypeId, data)
				this.stages.push(created)
				return created
			} catch (error) {
				this.error = error.response?.data?.error || 'Erro ao criar etapa'
				throw error
			}
		},

		async update(id, data) {
			try {
				const updated = await stageApi.update(id, data)
				const index = this.stages.findIndex(s => s.id === id)
				if (index !== -1) {
					this.stages[index] = updated
				}
				return updated
			} catch (error) {
				this.error = error.response?.data?.error || 'Erro ao atualizar etapa'
				throw error
			}
		},

		async remove(id) {
			try {
				await stageApi.delete(id)
				this.stages = this.stages.filter(s => s.id !== id)
			} catch (error) {
				this.error = error.response?.data?.error || 'Erro ao excluir etapa'
				throw error
			}
		},

		async reorder(procTypeId, stageIds) {
			try {
				await stageApi.reorder(procTypeId, stageIds)
				// Update local sort order to match the new order
				stageIds.forEach((stageId, index) => {
					const stage = this.stages.find(s => s.id === stageId)
					if (stage) {
						stage.sortOrder = index
					}
				})
			} catch (error) {
				this.error = error.response?.data?.error || 'Erro ao reordenar etapas'
				throw error
			}
		},
	},
})
