<?php

declare(strict_types=1);

return [
	'routes' => [
		// === Page (SPA entry point) ===
		['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],
		['name' => 'page#index', 'url' => '/{path}', 'verb' => 'GET',
			'requirements' => ['path' => '.+'], 'postfix' => 'catchall'],

		// === Process Types ===
		['name' => 'processType#index', 'url' => '/api/v1/process-types', 'verb' => 'GET'],
		['name' => 'processType#show', 'url' => '/api/v1/process-types/{id}', 'verb' => 'GET'],
		['name' => 'processType#create', 'url' => '/api/v1/process-types', 'verb' => 'POST'],
		['name' => 'processType#update', 'url' => '/api/v1/process-types/{id}', 'verb' => 'PUT'],
		['name' => 'processType#destroy', 'url' => '/api/v1/process-types/{id}', 'verb' => 'DELETE'],

		// === Stages ===
		['name' => 'stage#index', 'url' => '/api/v1/process-types/{processTypeId}/stages', 'verb' => 'GET'],
		['name' => 'stage#show', 'url' => '/api/v1/stages/{id}', 'verb' => 'GET'],
		['name' => 'stage#create', 'url' => '/api/v1/process-types/{processTypeId}/stages', 'verb' => 'POST'],
		['name' => 'stage#update', 'url' => '/api/v1/stages/{id}', 'verb' => 'PUT'],
		['name' => 'stage#destroy', 'url' => '/api/v1/stages/{id}', 'verb' => 'DELETE'],
		['name' => 'stage#reorder', 'url' => '/api/v1/process-types/{processTypeId}/stages/reorder', 'verb' => 'PUT'],

		// === Form Templates ===
		['name' => 'formTemplate#index', 'url' => '/api/v1/process-types/{processTypeId}/form-templates', 'verb' => 'GET'],
		['name' => 'formTemplate#show', 'url' => '/api/v1/form-templates/{id}', 'verb' => 'GET'],
		['name' => 'formTemplate#create', 'url' => '/api/v1/process-types/{processTypeId}/form-templates', 'verb' => 'POST'],
		['name' => 'formTemplate#update', 'url' => '/api/v1/form-templates/{id}', 'verb' => 'PUT'],
		['name' => 'formTemplate#destroy', 'url' => '/api/v1/form-templates/{id}', 'verb' => 'DELETE'],

		// === Form Fields ===
		['name' => 'formField#index', 'url' => '/api/v1/form-templates/{formTemplateId}/fields', 'verb' => 'GET'],
		['name' => 'formField#create', 'url' => '/api/v1/form-templates/{formTemplateId}/fields', 'verb' => 'POST'],
		['name' => 'formField#update', 'url' => '/api/v1/form-fields/{id}', 'verb' => 'PUT'],
		['name' => 'formField#destroy', 'url' => '/api/v1/form-fields/{id}', 'verb' => 'DELETE'],
		['name' => 'formField#reorder', 'url' => '/api/v1/form-templates/{formTemplateId}/fields/reorder', 'verb' => 'PUT'],

		// === Requests (process instances / cards) ===
		['name' => 'request#index', 'url' => '/api/v1/process-types/{processTypeId}/requests', 'verb' => 'GET'],
		['name' => 'request#show', 'url' => '/api/v1/requests/{id}', 'verb' => 'GET'],
		['name' => 'request#create', 'url' => '/api/v1/process-types/{processTypeId}/requests', 'verb' => 'POST'],
		['name' => 'request#update', 'url' => '/api/v1/requests/{id}', 'verb' => 'PUT'],
		['name' => 'request#destroy', 'url' => '/api/v1/requests/{id}', 'verb' => 'DELETE'],
		['name' => 'request#move', 'url' => '/api/v1/requests/{id}/move', 'verb' => 'PUT'],
		['name' => 'request#search', 'url' => '/api/v1/requests/search', 'verb' => 'GET'],
		['name' => 'request#byProtocol', 'url' => '/api/v1/requests/protocol/{protocolNumber}', 'verb' => 'GET'],
		['name' => 'request#history', 'url' => '/api/v1/requests/{id}/history', 'verb' => 'GET'],

		// === Card Operations (assignments, labels, etc.) ===
		['name' => 'card#assign', 'url' => '/api/v1/requests/{requestId}/assign', 'verb' => 'POST'],
		['name' => 'card#unassign', 'url' => '/api/v1/requests/{requestId}/assign/{userId}', 'verb' => 'DELETE'],
		['name' => 'card#addLabel', 'url' => '/api/v1/requests/{requestId}/labels', 'verb' => 'POST'],
		['name' => 'card#removeLabel', 'url' => '/api/v1/requests/{requestId}/labels/{labelId}', 'verb' => 'DELETE'],
		['name' => 'card#setDeadline', 'url' => '/api/v1/requests/{requestId}/deadline', 'verb' => 'PUT'],
		['name' => 'card#reorder', 'url' => '/api/v1/requests/{requestId}/reorder', 'verb' => 'PUT'],

		// === Labels ===
		['name' => 'label#index', 'url' => '/api/v1/labels', 'verb' => 'GET'],
		['name' => 'label#create', 'url' => '/api/v1/labels', 'verb' => 'POST'],
		['name' => 'label#update', 'url' => '/api/v1/labels/{id}', 'verb' => 'PUT'],
		['name' => 'label#destroy', 'url' => '/api/v1/labels/{id}', 'verb' => 'DELETE'],

		// === Config / License ===
		['name' => 'config#get', 'url' => '/api/v1/config', 'verb' => 'GET'],
		['name' => 'config#setLicense', 'url' => '/api/v1/config/license', 'verb' => 'PUT'],
		['name' => 'config#update', 'url' => '/api/v1/config/{key}', 'verb' => 'PUT'],
	],
];
