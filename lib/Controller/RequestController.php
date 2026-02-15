<?php

declare(strict_types=1);

namespace OCA\Tramita\Controller;

use OCA\Tramita\Exception\NotFoundException;
use OCA\Tramita\Exception\ValidationException;
use OCA\Tramita\Service\ProtocolService;
use OCA\Tramita\Service\RequestService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class RequestController extends Controller {
	public function __construct(
		string $appName,
		IRequest $request,
		private RequestService $service,
		private ProtocolService $protocolService,
		private string $userId,
	) {
		parent::__construct($appName, $request);
	}

	/**
	 * List requests for a process type, optionally filtered by status.
	 */
	#[NoAdminRequired]
	public function index(int $procTypeId, ?string $status = null, int $limit = 50, int $offset = 0): DataResponse {
		$requests = $this->service->findByProcessType($procTypeId, $status, $limit, $offset);
		return new DataResponse($requests);
	}

	/**
	 * Get a single request by ID.
	 */
	#[NoAdminRequired]
	public function show(int $id): DataResponse {
		try {
			$request = $this->service->findById($id);
			return new DataResponse($request);
		} catch (NotFoundException $e) {
			return new DataResponse(['error' => 'Not found'], Http::STATUS_NOT_FOUND);
		}
	}

	/**
	 * Create a new request (Kanban card) for a process type.
	 */
	#[NoAdminRequired]
	public function create(
		int $procTypeId,
		string $title,
		string $groupId,
		?string $description = null,
		?int $priority = null,
		?string $dueDate = null,
		?bool $isConfidential = null,
		?string $requesterName = null,
	): DataResponse {
		try {
			$request = $this->service->create(
				$procTypeId,
				$title,
				$this->userId,
				$groupId,
				$description,
				$priority,
				$dueDate,
				$isConfidential,
				$requesterName,
			);
			return new DataResponse($request, Http::STATUS_CREATED);
		} catch (ValidationException $e) {
			return new DataResponse(
				['error' => $e->getMessage(), 'errors' => $e->getErrors()],
				Http::STATUS_BAD_REQUEST,
			);
		} catch (NotFoundException $e) {
			return new DataResponse(['error' => $e->getMessage()], Http::STATUS_NOT_FOUND);
		}
	}

	/**
	 * Update an existing request.
	 */
	#[NoAdminRequired]
	public function update(
		int $id,
		?string $title = null,
		?string $description = null,
		?int $priority = null,
		?string $dueDate = null,
		?bool $isConfidential = null,
		?string $status = null,
		?int $sortOrder = null,
		?string $metadata = null,
	): DataResponse {
		try {
			$request = $this->service->update(
				$id,
				$title,
				$description,
				$priority,
				$dueDate,
				$isConfidential,
				$status,
				$sortOrder,
				$metadata,
			);
			return new DataResponse($request);
		} catch (NotFoundException $e) {
			return new DataResponse(['error' => 'Not found'], Http::STATUS_NOT_FOUND);
		} catch (ValidationException $e) {
			return new DataResponse(
				['error' => $e->getMessage(), 'errors' => $e->getErrors()],
				Http::STATUS_BAD_REQUEST,
			);
		}
	}

	/**
	 * Soft delete a request.
	 */
	#[NoAdminRequired]
	public function destroy(int $id): DataResponse {
		try {
			$request = $this->service->delete($id);
			return new DataResponse($request);
		} catch (NotFoundException $e) {
			return new DataResponse(['error' => 'Not found'], Http::STATUS_NOT_FOUND);
		}
	}

	/**
	 * Move a request (card) to a different stage (Kanban column).
	 */
	#[NoAdminRequired]
	public function move(int $id, int $toStageId, ?string $comment = null): DataResponse {
		try {
			$request = $this->service->move($id, $toStageId, $this->userId, $comment);
			return new DataResponse($request);
		} catch (NotFoundException $e) {
			return new DataResponse(['error' => 'Not found'], Http::STATUS_NOT_FOUND);
		} catch (ValidationException $e) {
			return new DataResponse(
				['error' => $e->getMessage(), 'errors' => $e->getErrors()],
				Http::STATUS_BAD_REQUEST,
			);
		}
	}

	/**
	 * Search requests by title/description within a group.
	 */
	#[NoAdminRequired]
	public function search(string $query, string $groupId, int $limit = 20): DataResponse {
		$results = $this->service->search($query, $groupId, $limit);
		return new DataResponse($results);
	}

	/**
	 * Find a request by its protocol number.
	 */
	#[NoAdminRequired]
	public function byProtocol(string $protocolNumber): DataResponse {
		try {
			$protocol = $this->protocolService->findByFullNumber($protocolNumber);
			if ($protocol->getRequestId() === null) {
				return new DataResponse(['error' => 'Not found'], Http::STATUS_NOT_FOUND);
			}
			$request = $this->service->findById($protocol->getRequestId());
			return new DataResponse($request);
		} catch (\OCP\AppFramework\Db\DoesNotExistException $e) {
			return new DataResponse(['error' => 'Not found'], Http::STATUS_NOT_FOUND);
		} catch (NotFoundException $e) {
			return new DataResponse(['error' => 'Not found'], Http::STATUS_NOT_FOUND);
		}
	}

	/**
	 * Get the activity/history log for a request.
	 * Placeholder: returns an empty array for now.
	 */
	#[NoAdminRequired]
	public function history(int $id): DataResponse {
		try {
			// Validate the request exists
			$this->service->findById($id);
			// Placeholder: activity log not yet implemented
			return new DataResponse([]);
		} catch (NotFoundException $e) {
			return new DataResponse(['error' => 'Not found'], Http::STATUS_NOT_FOUND);
		}
	}
}
