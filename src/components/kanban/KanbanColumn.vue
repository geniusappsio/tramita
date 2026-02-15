<template>
	<div class="kanban-column" :style="columnStyle">
		<div class="kanban-column__header">
			<div class="kanban-column__color" :style="{ backgroundColor: stage.color || '#808080' }" />
			<h3 class="kanban-column__title">{{ stage.name }}</h3>
			<span class="kanban-column__count">{{ requests.length }}</span>
		</div>

		<draggable
			:value="requests"
			group="requests"
			class="kanban-column__cards"
			ghost-class="kanban-card--ghost"
			:data-stage-id="stage.id"
			@end="onDragEnd">
			<KanbanCard
				v-for="request in requests"
				:key="request.id"
				:request="request"
				@click.native="$emit('click-card', request)" />
		</draggable>
	</div>
</template>

<script>
import draggable from 'vuedraggable'
import KanbanCard from './KanbanCard.vue'

export default {
	name: 'KanbanColumn',
	components: {
		draggable,
		KanbanCard,
	},
	props: {
		stage: {
			type: Object,
			required: true,
		},
		requests: {
			type: Array,
			default: () => [],
		},
	},
	computed: {
		columnStyle() {
			return {
				'--column-color': this.stage.color || '#808080',
			}
		},
	},
	methods: {
		onDragEnd(evt) {
			if (evt.to !== evt.from || evt.oldIndex !== evt.newIndex) {
				const requestId = parseInt(evt.item.dataset.requestId)
				const toStageId = parseInt(evt.to.dataset.stageId)
				this.$emit('move', { requestId, toStageId })
			}
		},
	},
}
</script>

<style scoped lang="scss">
.kanban-column {
	min-width: 280px;
	max-width: 320px;
	background: var(--color-background-dark);
	border-radius: var(--border-radius-large);
	display: flex;
	flex-direction: column;
	max-height: calc(100vh - 200px);

	&__header {
		display: flex;
		align-items: center;
		gap: 8px;
		padding: 12px 16px;
		border-bottom: 2px solid var(--column-color, #808080);
	}

	&__color {
		width: 10px;
		height: 10px;
		border-radius: 50%;
		flex-shrink: 0;
	}

	&__title {
		flex: 1;
		margin: 0;
		font-size: 14px;
		font-weight: 600;
	}

	&__count {
		background: var(--color-background-darker);
		padding: 2px 8px;
		border-radius: var(--border-radius-pill);
		font-size: 12px;
		font-weight: 600;
	}

	&__cards {
		flex: 1;
		overflow-y: auto;
		padding: 8px;
		display: flex;
		flex-direction: column;
		gap: 8px;
		min-height: 50px;
	}
}
</style>
