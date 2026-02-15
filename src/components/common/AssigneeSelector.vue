<template>
	<div class="assignee-selector">
		<div v-for="assignment in assignments" :key="assignment.id" class="assignee-item">
			<span class="assignee-item__name">{{ assignment.userId }}</span>
			<span class="assignee-item__role">{{ roleLabel(assignment.role) }}</span>
			<button class="assignee-item__remove" @click="$emit('unassign', { userId: assignment.userId })">
				&times;
			</button>
		</div>
		<div class="assignee-selector__add">
			<input v-model="newUserId" type="text" placeholder="ID do usu&aacute;rio" class="assignee-input">
			<select v-model="newRole" class="role-select">
				<option value="assigned">Respons&aacute;vel</option>
				<option value="reviewer">Revisor</option>
				<option value="approver">Aprovador</option>
			</select>
			<NcButton type="secondary" :disabled="!newUserId" @click="addAssignee">
				<template #icon>
					<Plus :size="16" />
				</template>
			</NcButton>
		</div>
	</div>
</template>

<script>
import NcButton from '@nextcloud/vue/dist/Components/NcButton.js'
import Plus from 'vue-material-design-icons/Plus.vue'

export default {
	name: 'AssigneeSelector',
	components: { NcButton, Plus },
	props: {
		requestId: { type: Number, default: null },
		assignments: { type: Array, default: () => [] },
	},
	data() {
		return {
			newUserId: '',
			newRole: 'assigned',
		}
	},
	methods: {
		roleLabel(role) {
			const labels = { assigned: 'Respons\u00e1vel', reviewer: 'Revisor', approver: 'Aprovador' }
			return labels[role] || role
		},
		addAssignee() {
			if (this.newUserId.trim()) {
				this.$emit('assign', { userId: this.newUserId.trim(), role: this.newRole })
				this.newUserId = ''
			}
		},
	},
}
</script>

<style scoped lang="scss">
.assignee-selector {
	&__add {
		display: flex;
		gap: 4px;
		margin-top: 8px;
	}
}

.assignee-item {
	display: flex;
	align-items: center;
	gap: 6px;
	padding: 4px 0;

	&__name {
		font-weight: 500;
		font-size: 14px;
	}

	&__role {
		font-size: 11px;
		padding: 1px 6px;
		border-radius: var(--border-radius-pill);
		background: var(--color-background-dark);
		color: var(--color-text-maxcontrast);
	}

	&__remove {
		background: none;
		border: none;
		cursor: pointer;
		color: var(--color-text-maxcontrast);
		font-size: 16px;
		padding: 0 4px;

		&:hover {
			color: var(--color-error);
		}
	}
}

.assignee-input {
	flex: 1;
	padding: 6px 8px;
	border: 1px solid var(--color-border-dark);
	border-radius: var(--border-radius);
	font-size: 13px;
}

.role-select {
	padding: 6px 8px;
	border: 1px solid var(--color-border-dark);
	border-radius: var(--border-radius);
	font-size: 13px;
}
</style>
