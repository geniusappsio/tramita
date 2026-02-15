<?php

declare(strict_types=1);

namespace OCA\Tramita\Controller;

use OCA\Tramita\Exception\NotFoundException;
use OCA\Tramita\Service\RequestService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

/**
 * Handles card-specific operations: assignments, labels, deadlines, reordering.
 */
class CardController extends Controller {
	public function __construct(
		string $appName,
		IRequest $request,
		private RequestService $requestService,
		private string $userId,
	) {
		parent::__construct($appName, $request);
	}

	/**
	 * Assign a user to a request card.
	 * Placeholder: returns a success response.
	 */
	#[NoAdminRequired]
	public function assign(int $requestId, string $userId, string $role = 'assigned'): DataResponse {
		try {
			// Validate the request exists
			$this->requestService->findById($requestId);

			// Placeholder: assignment logic not yet implemented
			return new DataResponse([
				'status' => 'ok',
				'requestId' => $requestId,
				'userId' => $userId,
				'role' => $role,
			]);
		} catch (NotFoundException $e) {
			return new DataResponse(['error' => 'Not found'], Http::STATUS_NOT_FOUND);
		}
	}

	/**
	 * Remove a user assignment from a request card.
	 * Placeholder: returns a success response.
	 */
	#[NoAdminRequired]
	public function unassign(int $requestId, string $userId): DataResponse {
		try {
			// Validate the request exists
			$this->requestService->findById($requestId);

			// Placeholder: unassignment logic not yet implemented
			return new DataResponse([
				'status' => 'ok',
				'requestId' => $requestId,
				'userId' => $userId,
			]);
		} catch (NotFoundException $e) {
			return new DataResponse(['error' => 'Not found'], Http::STATUS_NOT_FOUND);
		}
	}

	/**
	 * Add a label to a request card.
	 * Placeholder: returns a success response.
	 */
	#[NoAdminRequired]
	public function addLabel(int $requestId, int $labelId): DataResponse {
		try {
			// Validate the request exists
			$this->requestService->findById($requestId);

			// Placeholder: label logic not yet implemented
			return new DataResponse([
				'status' => 'ok',
				'requestId' => $requestId,
				'labelId' => $labelId,
			]);
		} catch (NotFoundException $e) {
			return new DataResponse(['error' => 'Not found'], Http::STATUS_NOT_FOUND);
		}
	}

	/**
	 * Remove a label from a request card.
	 * Placeholder: returns a success response.
	 */
	#[NoAdminRequired]
	public function removeLabel(int $requestId, int $labelId): DataResponse {
		try {
			// Validate the request exists
			$this->requestService->findById($requestId);

			// Placeholder: label removal logic not yet implemented
			return new DataResponse([
				'status' => 'ok',
				'requestId' => $requestId,
				'labelId' => $labelId,
			]);
		} catch (NotFoundException $e) {
			return new DataResponse(['error' => 'Not found'], Http::STATUS_NOT_FOUND);
		}
	}

	/**
	 * Set or clear the deadline (due_date) on a request card.
	 */
	#[NoAdminRequired]
	public function setDeadline(int $requestId, ?string $dueDate = null): DataResponse {
		try {
			$request = $this->requestService->update($requestId, dueDate: $dueDate);
			return new DataResponse($request);
		} catch (NotFoundException $e) {
			return new DataResponse(['error' => 'Not found'], Http::STATUS_NOT_FOUND);
		}
	}

	/**
	 * Reorder a card within its current stage column.
	 * Placeholder: returns a success response.
	 */
	#[NoAdminRequired]
	public function reorder(int $requestId, int $sortOrder): DataResponse {
		try {
			$request = $this->requestService->update($requestId, sortOrder: $sortOrder);
			return new DataResponse($request);
		} catch (NotFoundException $e) {
			return new DataResponse(['error' => 'Not found'], Http::STATUS_NOT_FOUND);
		}
	}
}
