<template>
	<div class="dynamic-form">
		<div v-for="field in visibleFields"
			:key="field.id"
			:class="['form-field', `form-field--${field.width || 'full'}`]">
			<!-- Label (skip for checkbox, it has its own) -->
			<label v-if="field.fieldType !== 'checkbox'"
				:for="`field-${field.name}`"
				class="form-field__label">
				{{ field.label }}
				<span v-if="field.isRequired" class="required">*</span>
			</label>

			<!-- Text-like inputs: text, email, phone, cpf, cnpj -->
			<input v-if="isTextInput(field.fieldType)"
				:id="`field-${field.name}`"
				:type="getInputType(field.fieldType)"
				:value="values[field.name]"
				:placeholder="field.placeholder"
				:required="field.isRequired"
				:readonly="readonly"
				class="form-field__input"
				@input="updateValue(field.name, $event.target.value)">

			<!-- Number / Currency -->
			<input v-else-if="field.fieldType === 'number' || field.fieldType === 'currency'"
				:id="`field-${field.name}`"
				type="number"
				:value="values[field.name]"
				:placeholder="field.placeholder"
				:required="field.isRequired"
				:readonly="readonly"
				:step="field.fieldType === 'currency' ? '0.01' : 'any'"
				class="form-field__input"
				@input="updateValue(field.name, $event.target.value)">

			<!-- Date -->
			<input v-else-if="field.fieldType === 'date'"
				:id="`field-${field.name}`"
				type="date"
				:value="values[field.name]"
				:placeholder="field.placeholder"
				:required="field.isRequired"
				:readonly="readonly"
				class="form-field__input"
				@input="updateValue(field.name, $event.target.value)">

			<!-- Textarea -->
			<textarea v-else-if="field.fieldType === 'textarea'"
				:id="`field-${field.name}`"
				:value="values[field.name]"
				:placeholder="field.placeholder"
				:required="field.isRequired"
				:readonly="readonly"
				rows="4"
				class="form-field__textarea"
				@input="updateValue(field.name, $event.target.value)" />

			<!-- Select -->
			<select v-else-if="field.fieldType === 'select'"
				:id="`field-${field.name}`"
				:value="values[field.name]"
				:required="field.isRequired"
				:disabled="readonly"
				class="form-field__select"
				@change="updateValue(field.name, $event.target.value)">
				<option value="">
					{{ field.placeholder || 'Selecione...' }}
				</option>
				<option v-for="opt in (field.options || [])"
					:key="opt.value"
					:value="opt.value">
					{{ opt.label }}
				</option>
			</select>

			<!-- Checkbox -->
			<label v-else-if="field.fieldType === 'checkbox'"
				:for="`field-${field.name}`"
				class="form-field__checkbox-label">
				<input :id="`field-${field.name}`"
					type="checkbox"
					:checked="values[field.name]"
					:disabled="readonly"
					@change="updateValue(field.name, $event.target.checked)">
				{{ field.label }}
				<span v-if="field.isRequired" class="required">*</span>
			</label>

			<!-- Radio -->
			<div v-else-if="field.fieldType === 'radio'" class="form-field__radio-group">
				<label v-for="opt in (field.options || [])"
					:key="opt.value"
					class="form-field__radio-label">
					<input type="radio"
						:name="`field-${field.name}`"
						:value="opt.value"
						:checked="values[field.name] === opt.value"
						:disabled="readonly"
						@change="updateValue(field.name, opt.value)">
					{{ opt.label }}
				</label>
			</div>

			<!-- File (placeholder) -->
			<input v-else-if="field.fieldType === 'file'"
				:id="`field-${field.name}`"
				type="file"
				:required="field.isRequired"
				:disabled="readonly"
				class="form-field__input"
				@change="updateValue(field.name, $event.target.files)">

			<!-- User select (placeholder - renders as text input) -->
			<input v-else-if="field.fieldType === 'user_select'"
				:id="`field-${field.name}`"
				type="text"
				:value="values[field.name]"
				:placeholder="field.placeholder || 'Buscar usuário...'"
				:required="field.isRequired"
				:readonly="readonly"
				class="form-field__input"
				@input="updateValue(field.name, $event.target.value)">

			<!-- Help text -->
			<p v-if="field.helpText" class="form-field__help">
				{{ field.helpText }}
			</p>

			<!-- Error message -->
			<p v-if="errors[field.name]" class="form-field__error">
				{{ errors[field.name] }}
			</p>
		</div>
	</div>
</template>

<script>
export default {
	name: 'DynamicForm',

	props: {
		fields: {
			type: Array,
			required: true,
		},
		values: {
			type: Object,
			default: () => ({}),
		},
		readonly: {
			type: Boolean,
			default: false,
		},
		errors: {
			type: Object,
			default: () => ({}),
		},
	},

	computed: {
		/**
		 * Filter fields based on conditional visibility and hidden flag.
		 */
		visibleFields() {
			return this.fields.filter(field => {
				// Skip hidden fields
				if (field.isHidden) {
					return false
				}

				// Check conditional visibility
				if (field.conditional && Object.keys(field.conditional).length > 0) {
					return this.evaluateConditional(field.conditional)
				}

				return true
			})
		},
	},

	methods: {
		/**
		 * Emit updated values object when a field value changes.
		 */
		updateValue(fieldName, value) {
			this.$emit('update:values', {
				...this.values,
				[fieldName]: value,
			})
		},

		/**
		 * Determine if a field type should render as a text input.
		 */
		isTextInput(fieldType) {
			return ['text', 'email', 'phone', 'cpf', 'cnpj'].includes(fieldType)
		},

		/**
		 * Map field types to HTML input types.
		 */
		getInputType(fieldType) {
			const typeMap = {
				text: 'text',
				email: 'email',
				phone: 'tel',
				cpf: 'text',
				cnpj: 'text',
			}
			return typeMap[fieldType] || 'text'
		},

		/**
		 * Evaluate conditional visibility rules.
		 * Supports: { field: "field_name", operator: "equals|not_equals|filled|empty", value: "..." }
		 */
		evaluateConditional(conditional) {
			if (!conditional || !conditional.field) {
				return true
			}

			const fieldValue = this.values[conditional.field]
			const operator = conditional.operator || 'equals'
			const targetValue = conditional.value

			switch (operator) {
			case 'equals':
				return fieldValue === targetValue
			case 'not_equals':
				return fieldValue !== targetValue
			case 'filled':
				return fieldValue !== undefined && fieldValue !== null && fieldValue !== ''
			case 'empty':
				return fieldValue === undefined || fieldValue === null || fieldValue === ''
			default:
				return true
			}
		},
	},
}
</script>

<style lang="scss" scoped>
.dynamic-form {
	display: flex;
	flex-wrap: wrap;
	gap: 16px;
}

.form-field {
	min-width: 0;

	&--full {
		width: 100%;
		flex-basis: 100%;
	}

	&--half {
		width: calc(50% - 8px);
		flex-basis: calc(50% - 8px);
	}

	&--third {
		width: calc(33.333% - 11px);
		flex-basis: calc(33.333% - 11px);
	}

	&__label {
		display: block;
		font-weight: 500;
		margin-bottom: 4px;
		font-size: 14px;

		.required {
			color: var(--color-error);
			margin-left: 2px;
		}
	}

	&__input,
	&__textarea,
	&__select {
		width: 100%;
		padding: 8px 12px;
		border: 1px solid var(--color-border);
		border-radius: var(--border-radius);
		background: var(--color-main-background);
		font-size: 14px;
		color: var(--color-main-text);
		transition: border-color 0.15s;

		&:focus {
			border-color: var(--color-primary-element);
			outline: none;
		}

		&:read-only {
			background: var(--color-background-dark);
			cursor: not-allowed;
		}

		&:disabled {
			background: var(--color-background-dark);
			cursor: not-allowed;
			opacity: 0.7;
		}
	}

	&__textarea {
		resize: vertical;
		min-height: 80px;
	}

	&__select {
		appearance: auto;
	}

	&__checkbox-label {
		display: flex;
		align-items: center;
		gap: 8px;
		cursor: pointer;
		font-size: 14px;
		padding: 8px 0;

		input[type='checkbox'] {
			width: auto;
			margin: 0;
		}

		.required {
			color: var(--color-error);
			margin-left: 2px;
		}
	}

	&__radio-group {
		display: flex;
		flex-direction: column;
		gap: 8px;
		padding: 4px 0;
	}

	&__radio-label {
		display: flex;
		align-items: center;
		gap: 8px;
		cursor: pointer;
		font-size: 14px;

		input[type='radio'] {
			width: auto;
			margin: 0;
		}
	}

	&__help {
		margin: 4px 0 0;
		font-size: 12px;
		color: var(--color-text-maxcontrast);
		line-height: 1.4;
	}

	&__error {
		margin: 4px 0 0;
		font-size: 12px;
		color: var(--color-error);
		line-height: 1.4;
	}
}

// Responsive: stack all fields on narrow screens
@media (max-width: 600px) {
	.form-field {
		&--half,
		&--third {
			width: 100%;
			flex-basis: 100%;
		}
	}
}
</style>
