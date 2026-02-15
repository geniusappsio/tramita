<?php

declare(strict_types=1);

namespace OCA\Tramita\Service;

use OCA\Tramita\Db\Comment;
use OCA\Tramita\Db\CommentMapper;
use OCA\Tramita\Exception\NotFoundException;
use OCA\Tramita\Exception\ValidationException;

class CommentService {
	private CommentMapper $mapper;

	public function __construct(CommentMapper $mapper) {
		$this->mapper = $mapper;
	}

	/**
	 * Find all non-deleted comments for a given request, ordered by created_at ASC.
	 *
	 * @param int $requestId
	 * @return Comment[]
	 */
	public function findByRequest(int $requestId): array {
		return $this->mapper->findByRequest($requestId);
	}

	/**
	 * Find a comment by its ID.
	 *
	 * @param int $id
	 * @return Comment
	 * @throws NotFoundException
	 */
	public function findById(int $id): Comment {
		try {
			return $this->mapper->findById($id);
		} catch (\OCP\AppFramework\Db\DoesNotExistException $e) {
			throw new NotFoundException('Comment not found: ' . $id);
		}
	}

	/**
	 * Create a new comment on a request.
	 *
	 * @param int $requestId
	 * @param string $userId
	 * @param string $content
	 * @param int|null $parentId
	 * @param array|null $mentions
	 * @return Comment
	 * @throws ValidationException
	 */
	public function create(
		int $requestId,
		string $userId,
		string $content,
		?int $parentId = null,
		?array $mentions = null
	): Comment {
		$this->validateContent($content);

		$now = new \DateTimeImmutable();

		$comment = new Comment();
		$comment->setRequestId($requestId);
		$comment->setUserId($userId);
		$comment->setContent($content);
		$comment->setParentId($parentId);
		$comment->setIsSystem(false);
		$comment->setMentions($mentions !== null ? json_encode($mentions) : null);
		$comment->setCreatedAt($now->format('Y-m-d H:i:s'));
		$comment->setUpdatedAt($now->format('Y-m-d H:i:s'));

		return $this->mapper->insert($comment);
	}

	/**
	 * Update the content of an existing comment.
	 *
	 * @param int $id
	 * @param string $content
	 * @return Comment
	 * @throws NotFoundException
	 * @throws ValidationException
	 */
	public function update(int $id, string $content): Comment {
		$this->validateContent($content);

		$comment = $this->findById($id);

		$comment->setContent($content);

		$now = new \DateTimeImmutable();
		$comment->setUpdatedAt($now->format('Y-m-d H:i:s'));

		return $this->mapper->update($comment);
	}

	/**
	 * Soft delete a comment by setting deleted_at.
	 *
	 * @param int $id
	 * @return Comment
	 * @throws NotFoundException
	 */
	public function delete(int $id): Comment {
		$comment = $this->findById($id);

		$now = new \DateTimeImmutable();
		$comment->setDeletedAt($now->format('Y-m-d H:i:s'));
		$comment->setUpdatedAt($now->format('Y-m-d H:i:s'));

		return $this->mapper->update($comment);
	}

	/**
	 * Validate that the content is not empty.
	 *
	 * @param string $content
	 * @throws ValidationException
	 */
	private function validateContent(string $content): void {
		if (trim($content) === '') {
			throw new ValidationException('Validation failed', [
				'content' => 'Content is required',
			]);
		}
	}
}
