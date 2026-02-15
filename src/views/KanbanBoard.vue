<template>
	<div class="kanban-board">
		<div class="kanban-board__header">
			<h2>{{ processType?.name || 'Carregando...' }}</h2>
			<NcButton type="primary" @click="createRequest">
				<template #icon>
					<Plus :size="20" />
				</template>
				Nova Requisição
			</NcButton>
		</div>

		<NcLoadingIcon v-if="loading" :size="44" />

		<div v-else class="kanban-board__columns">
			<KanbanColumn
				v-for="stage in stages"
				:key="stage.id"
				:stage="stage"
				:requests="getRequestsByStage(stage.id)"
				@move="onMoveRequest"
				@click-card="onClickCard" />
		</div>
	</div>
</template>

<script>
import NcButton from '@nextcloud/vue/dist/Components/NcButton.js'
import NcLoadingIcon from '@nextcloud/vue/dist/Components/NcLoadingIcon.js'
import Plus from 'vue-material-design-icons/Plus.vue'
import KanbanColumn from '../components/kanban/KanbanColumn.vue'
import { useProcessTypeStore } from '../store/processType.js'
import { useStageStore } from '../store/stage.js'
import { useRequestStore } from '../store/request.js'
import { showError } from '@nextcloud/dialogs'

export default {
	name: 'KanbanBoard',
	components: {
		NcButton,
		NcLoadingIcon,
		Plus,
		KanbanColumn,
	},
	data() {
		return {
			processTypeStore: useProcessTypeStore(),
			stageStore: useStageStore(),
			requestStore: useRequestStore(),
			loading: true,
		}
	},
	computed: {
		processTypeId() {
			return parseInt(this.$route.params.processTypeId)
		},
		processType() {
			return this.processTypeStore.getById(this.processTypeId)
		},
		stages() {
			return this.stageStore.getByProcessType(this.processTypeId)
				.sort((a, b) => a.sortOrder - b.sortOrder)
		},
	},
	async created() {
		try {
			await Promise.all([
				this.processTypeStore.fetchById(this.processTypeId),
				this.stageStore.fetchByProcessType(this.processTypeId),
				this.requestStore.fetchByProcessType(this.processTypeId),
			])
		} catch (error) {
			showError('Erro ao carregar o board')
		} finally {
			this.loading = false
		}
	},
	methods: {
		getRequestsByStage(stageId) {
			return this.requestStore.getByStage(stageId)
				.sort((a, b) => a.sortOrder - b.sortOrder)
		},
		createRequest() {
			this.$router.push(`/request/new/${this.processTypeId}`)
		},
		onClickCard(request) {
			this.$router.push(`/request/${request.id}`)
		},
		async onMoveRequest({ requestId, toStageId }) {
			try {
				await this.requestStore.move(requestId, toStageId)
			} catch (error) {
				showError('Erro ao mover requisição')
			}
		},
	},
}
</script>

<style scoped lang="scss">
.kanban-board {
	padding: 20px;
	height: 100%;
	display: flex;
	flex-direction: column;

	&__header {
		display: flex;
		justify-content: space-between;
		align-items: center;
		margin-bottom: 16px;
		flex-shrink: 0;

		h2 {
			margin: 0;
		}
	}

	&__columns {
		display: flex;
		gap: 12px;
		overflow-x: auto;
		flex: 1;
		padding-bottom: 16px;
		align-items: flex-start;
	}
}
</style>
