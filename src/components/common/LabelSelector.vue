<template>
	<div class="label-selector">
		<div class="label-selector__current">
			<span v-for="label in selectedLabels" :key="label.id"
				class="label-chip" :style="{ backgroundColor: label.color }">
				{{ label.name }}
				<button class="label-chip__remove" @click="$emit('remove', label.id)">&times;</button>
			</span>
		</div>
		<div class="label-selector__add">
			<select @change="onAdd($event)">
				<option value="">+ Adicionar label</option>
				<option v-for="label in availableLabels" :key="label.id" :value="label.id">
					{{ label.name }}
				</option>
			</select>
		</div>
	</div>
</template>

<script>
import { useLabelStore } from '../../store/label.js'

export default {
	name: 'LabelSelector',
	props: {
		requestId: { type: Number, default: null },
		selectedLabels: { type: Array, default: () => [] },
		groupId: { type: String, default: '' },
	},
	data() {
		return {
			labelStore: useLabelStore(),
		}
	},
	computed: {
		availableLabels() {
			const selectedIds = this.selectedLabels.map(l => l.id)
			return this.labelStore.labels.filter(l => !selectedIds.includes(l.id))
		},
	},
	created() {
		if (this.labelStore.labels.length === 0) {
			this.labelStore.fetchAll({ groupId: this.groupId })
		}
	},
	methods: {
		onAdd(event) {
			const labelId = parseInt(event.target.value)
			if (labelId) {
				this.$emit('add', labelId)
				event.target.value = ''
			}
		},
	},
}
</script>

<style scoped lang="scss">
.label-selector {
	&__current {
		display: flex;
		flex-wrap: wrap;
		gap: 4px;
		margin-bottom: 8px;
	}

	&__add select {
		width: 100%;
		padding: 6px 8px;
		border: 1px solid var(--color-border-dark);
		border-radius: var(--border-radius);
		background: var(--color-main-background);
		font-size: 13px;
	}
}

.label-chip {
	display: inline-flex;
	align-items: center;
	gap: 4px;
	padding: 2px 8px;
	border-radius: var(--border-radius-pill);
	font-size: 12px;
	font-weight: 600;
	color: white;

	&__remove {
		background: none;
		border: none;
		color: white;
		cursor: pointer;
		font-size: 14px;
		line-height: 1;
		padding: 0;
		opacity: 0.7;

		&:hover {
			opacity: 1;
		}
	}
}
</style>
