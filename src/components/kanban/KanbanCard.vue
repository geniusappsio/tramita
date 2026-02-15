<template>
	<div class="kanban-card" :data-request-id="request.id" :class="cardClasses">
		<div class="kanban-card__header">
			<span v-if="request.protocolId" class="kanban-card__protocol">
				{{ request.protocolNumber || `#${request.protocolId}` }}
			</span>
			<span class="kanban-card__priority" :class="`priority--${request.priority}`">
				{{ priorityLabel }}
			</span>
		</div>

		<h4 class="kanban-card__title">{{ request.title }}</h4>

		<div class="kanban-card__footer">
			<span v-if="request.dueDate" class="kanban-card__due" :class="{ 'kanban-card__due--overdue': isOverdue }">
				<CalendarClock :size="14" />
				{{ formatDate(request.dueDate) }}
			</span>
			<span v-if="request.requesterName" class="kanban-card__requester">
				<AccountCircle :size="14" />
				{{ request.requesterName }}
			</span>
		</div>

		<div v-if="request.isConfidential" class="kanban-card__confidential">
			<Lock :size="14" />
		</div>
	</div>
</template>

<script>
import CalendarClock from 'vue-material-design-icons/CalendarClock.vue'
import AccountCircle from 'vue-material-design-icons/AccountCircle.vue'
import Lock from 'vue-material-design-icons/Lock.vue'

export default {
	name: 'KanbanCard',
	components: {
		CalendarClock,
		AccountCircle,
		Lock,
	},
	props: {
		request: {
			type: Object,
			required: true,
		},
	},
	computed: {
		priorityLabel() {
			const labels = { 1: 'Urgente', 2: 'Normal', 3: 'Baixa' }
			return labels[this.request.priority] || 'Normal'
		},
		isOverdue() {
			if (!this.request.dueDate) return false
			return new Date(this.request.dueDate) < new Date()
		},
		cardClasses() {
			return {
				'kanban-card--confidential': this.request.isConfidential,
				'kanban-card--overdue': this.isOverdue,
			}
		},
	},
	methods: {
		formatDate(dateStr) {
			if (!dateStr) return ''
			const date = new Date(dateStr)
			return date.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit' })
		},
	},
}
</script>

<style scoped lang="scss">
.kanban-card {
	background: var(--color-main-background);
	border-radius: var(--border-radius);
	padding: 10px 12px;
	cursor: pointer;
	border: 1px solid var(--color-border);
	transition: box-shadow 0.2s, border-color 0.2s;
	position: relative;

	&:hover {
		box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
		border-color: var(--color-primary-element);
	}

	&--ghost {
		opacity: 0.4;
		border: 2px dashed var(--color-primary-element);
	}

	&--overdue {
		border-left: 3px solid var(--color-error);
	}

	&__header {
		display: flex;
		justify-content: space-between;
		align-items: center;
		margin-bottom: 6px;
	}

	&__protocol {
		font-size: 11px;
		font-family: monospace;
		color: var(--color-text-maxcontrast);
	}

	&__priority {
		font-size: 10px;
		padding: 1px 6px;
		border-radius: var(--border-radius-pill);
		font-weight: 600;
		text-transform: uppercase;
	}

	&__title {
		margin: 0 0 8px;
		font-size: 13px;
		font-weight: 500;
		line-height: 1.3;
		display: -webkit-box;
		-webkit-line-clamp: 2;
		-webkit-box-orient: vertical;
		overflow: hidden;
	}

	&__footer {
		display: flex;
		gap: 12px;
		align-items: center;
		font-size: 12px;
		color: var(--color-text-maxcontrast);
	}

	&__due, &__requester {
		display: flex;
		align-items: center;
		gap: 3px;
	}

	&__due--overdue {
		color: var(--color-error);
		font-weight: 600;
	}

	&__confidential {
		position: absolute;
		top: 8px;
		right: 8px;
		color: var(--color-warning);
	}
}

.priority {
	&--1 {
		background: var(--color-error);
		color: white;
	}
	&--2 {
		background: var(--color-primary-element);
		color: white;
	}
	&--3 {
		background: var(--color-text-maxcontrast);
		color: white;
	}
}
</style>
