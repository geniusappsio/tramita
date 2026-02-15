<?php

declare(strict_types=1);

namespace OCA\Tramita\Service;

use OCA\Tramita\Db\FormField;
use OCA\Tramita\Db\FormFieldMapper;
use OCA\Tramita\Exception\NotFoundException;
use OCA\Tramita\Exception\ValidationException;
use OCP\AppFramework\Db\DoesNotExistException;

class FormFieldService {
	private FormFieldMapper $mapper;

	/** @var string[] Allowed field types */
	private const ALLOWED_FIELD_TYPES = [
		'text',
		'number',
		'date',
		'select',
		'textarea',
		'file',
		'checkbox',
		'radio',
		'email',
		'cpf',
		'cnpj',
		'phone',
		'currency',
		'user_select',
	];

	public function __construct(FormFieldMapper $mapper) {
		$this->mapper = $mapper;
	}

	/**
	 * Find all form fields for a given template.
	 *
	 * @param int $templateId
	 * @return FormField[]
	 */
	public function findByTemplate(int $templateId): array {
		return $this->mapper->findByTemplate($templateId);
	}

	/**
	 * Find a form field by its ID.
	 *
	 * @param int $id
	 * @return FormField
	 * @throws NotFoundException
	 */
	public function findById(int $id): FormField {
		try {
			return $this->mapper->findById($id);
		} catch (DoesNotExistException $e) {
			throw new NotFoundException('Form field not found: ' . $id);
		}
	}

	/**
	 * Create a new form field.
	 *
	 * @param int $templateId
	 * @param string $name
	 * @param string $label
	 * @param string $fieldType
	 * @param string|null $placeholder
	 * @param string|null $helpText
	 * @param string|null $defaultValue
	 * @param bool|null $isRequired
	 * @param bool|null $isReadonly
	 * @param bool|null $isHidden
	 * @param array|null $validation
	 * @param array|null $options
	 * @param string|null $width
	 * @param array|null $conditional
	 * @return FormField
	 * @throws ValidationException
	 */
	public function create(
		int $templateId,
		string $name,
		string $label,
		string $fieldType,
		?string $placeholder = null,
		?string $helpText = null,
		?string $defaultValue = null,
		?bool $isRequired = null,
		?bool $isReadonly = null,
		?bool $isHidden = null,
		?array $validation = null,
		?array $options = null,
		?string $width = null,
		?array $conditional = null
	): FormField {
		$this->validateRequired($name, $label, $fieldType);

		// Determine next sort order for this template
		$existing = $this->mapper->findByTemplate($templateId);
		$maxSort = 0;
		foreach ($existing as $field) {
			if ($field->getSortOrder() > $maxSort) {
				$maxSort = $field->getSortOrder();
			}
		}

		$now = new \DateTimeImmutable();

		$field = new FormField();
		$field->setTemplateId($templateId);
		$field->setName($name);
		$field->setLabel($label);
		$field->setFieldType($fieldType);
		$field->setPlaceholder($placeholder);
		$field->setHelpText($helpText);
		$field->setDefaultValue($defaultValue);
		$field->setIsRequired($isRequired ?? false);
		$field->setIsReadonly($isReadonly ?? false);
		$field->setIsHidden($isHidden ?? false);
		$field->setValidation($validation !== null ? json_encode($validation) : null);
		$field->setOptions($options !== null ? json_encode($options) : null);
		$field->setSortOrder($maxSort + 1);
		$field->setWidth($width ?? 'full');
		$field->setConditional($conditional !== null ? json_encode($conditional) : null);
		$field->setCreatedAt($now->format('Y-m-d H:i:s'));
		$field->setUpdatedAt($now->format('Y-m-d H:i:s'));

		return $this->mapper->insert($field);
	}

	/**
	 * Update an existing form field. Only non-null parameters are updated.
	 *
	 * @param int $id
	 * @param string|null $name
	 * @param string|null $label
	 * @param string|null $fieldType
	 * @param string|null $placeholder
	 * @param string|null $helpText
	 * @param string|null $defaultValue
	 * @param bool|null $isRequired
	 * @param bool|null $isReadonly
	 * @param bool|null $isHidden
	 * @param array|null $validation
	 * @param array|null $options
	 * @param string|null $width
	 * @param array|null $conditional
	 * @return FormField
	 * @throws NotFoundException
	 * @throws ValidationException
	 */
	public function update(
		int $id,
		?string $name = null,
		?string $label = null,
		?string $fieldType = null,
		?string $placeholder = null,
		?string $helpText = null,
		?string $defaultValue = null,
		?bool $isRequired = null,
		?bool $isReadonly = null,
		?bool $isHidden = null,
		?array $validation = null,
		?array $options = null,
		?string $width = null,
		?array $conditional = null
	): FormField {
		$field = $this->findById($id);

		if ($name !== null) {
			if (trim($name) === '') {
				throw new ValidationException('Validation failed', ['name' => 'Name is required']);
			}
			$field->setName($name);
		}

		if ($label !== null) {
			if (trim($label) === '') {
				throw new ValidationException('Validation failed', ['label' => 'Label is required']);
			}
			$field->setLabel($label);
		}

		if ($fieldType !== null) {
			if (!in_array($fieldType, self::ALLOWED_FIELD_TYPES, true)) {
				throw new ValidationException('Validation failed', [
					'fieldType' => 'Invalid field type: ' . $fieldType,
				]);
			}
			$field->setFieldType($fieldType);
		}

		if ($placeholder !== null) {
			$field->setPlaceholder($placeholder);
		}

		if ($helpText !== null) {
			$field->setHelpText($helpText);
		}

		if ($defaultValue !== null) {
			$field->setDefaultValue($defaultValue);
		}

		if ($isRequired !== null) {
			$field->setIsRequired($isRequired);
		}

		if ($isReadonly !== null) {
			$field->setIsReadonly($isReadonly);
		}

		if ($isHidden !== null) {
			$field->setIsHidden($isHidden);
		}

		if ($validation !== null) {
			$field->setValidation(json_encode($validation));
		}

		if ($options !== null) {
			$field->setOptions(json_encode($options));
		}

		if ($width !== null) {
			$field->setWidth($width);
		}

		if ($conditional !== null) {
			$field->setConditional(json_encode($conditional));
		}

		$now = new \DateTimeImmutable();
		$field->setUpdatedAt($now->format('Y-m-d H:i:s'));

		return $this->mapper->update($field);
	}

	/**
	 * Soft delete a form field by setting deleted_at.
	 *
	 * @param int $id
	 * @return FormField
	 * @throws NotFoundException
	 */
	public function delete(int $id): FormField {
		$field = $this->findById($id);

		$now = new \DateTimeImmutable();
		$field->setDeletedAt($now->format('Y-m-d H:i:s'));

		return $this->mapper->update($field);
	}

	/**
	 * Reorder fields within a template.
	 *
	 * @param int $templateId
	 * @param array $fieldIds Ordered array of field IDs
	 * @return void
	 * @throws NotFoundException
	 */
	public function reorder(int $templateId, array $fieldIds): void {
		$fields = $this->mapper->findByTemplate($templateId);
		$fieldMap = [];
		foreach ($fields as $field) {
			$fieldMap[$field->getId()] = $field;
		}

		$now = new \DateTimeImmutable();
		$sortOrder = 0;

		foreach ($fieldIds as $fieldId) {
			$sortOrder++;
			if (!isset($fieldMap[$fieldId])) {
				throw new NotFoundException('Form field not found: ' . $fieldId);
			}
			$field = $fieldMap[$fieldId];
			$field->setSortOrder($sortOrder);
			$field->setUpdatedAt($now->format('Y-m-d H:i:s'));
			$this->mapper->update($field);
		}
	}

	/**
	 * Validate required fields for creation.
	 *
	 * @param string $name
	 * @param string $label
	 * @param string $fieldType
	 * @throws ValidationException
	 */
	private function validateRequired(string $name, string $label, string $fieldType): void {
		$errors = [];

		if (trim($name) === '') {
			$errors['name'] = 'Name is required';
		}

		if (trim($label) === '') {
			$errors['label'] = 'Label is required';
		}

		if (!in_array($fieldType, self::ALLOWED_FIELD_TYPES, true)) {
			$errors['fieldType'] = 'Invalid field type: ' . $fieldType . '. Allowed: ' . implode(', ', self::ALLOWED_FIELD_TYPES);
		}

		if (!empty($errors)) {
			throw new ValidationException('Validation failed', $errors);
		}
	}
}
