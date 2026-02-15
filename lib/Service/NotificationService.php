<?php

declare(strict_types=1);

namespace OCA\Tramita\Service;

use OCA\Tramita\Db\Notification;
use OCA\Tramita\Db\NotificationMapper;
use OCA\Tramita\Db\NotifPref;
use OCA\Tramita\Db\NotifPrefMapper;
use OCP\AppFramework\Db\DoesNotExistException;

class NotificationService {

	public function __construct(
		private NotificationMapper $notificationMapper,
		private NotifPrefMapper $notifPrefMapper,
	) {
	}

	/**
	 * Create a notification for a user, respecting their preferences.
	 *
	 * If the user has disabled the given event type, the notification is skipped.
	 */
	public function notify(
		string $userId,
		string $type,
		string $title,
		?int $requestId = null,
		?string $message = null,
		?string $link = null,
	): ?Notification {
		// Check user preferences before creating
		try {
			$pref = $this->notifPrefMapper->findByUserAndEvent($userId, $type);
			if (!$pref->getIsEnabled()) {
				return null;
			}
		} catch (DoesNotExistException $e) {
			// No preference set — default is to notify
		}

		$now = new \DateTimeImmutable();

		$notification = new Notification();
		$notification->setUserId($userId);
		$notification->setType($type);
		$notification->setTitle($title);
		$notification->setRequestId($requestId);
		$notification->setMessage($message);
		$notification->setLink($link);
		$notification->setIsRead(false);
		$notification->setCreatedAt($now);

		return $this->notificationMapper->insert($notification);
	}

	/**
	 * Find notifications for a user with optional filtering.
	 *
	 * @return Notification[]
	 */
	public function findByUser(
		string $userId,
		bool $unreadOnly = false,
		int $limit = 50,
		int $offset = 0,
	): array {
		return $this->notificationMapper->findByUser($userId, $unreadOnly, $limit, $offset);
	}

	/**
	 * Mark a notification as read.
	 */
	public function markAsRead(int $id): void {
		$this->notificationMapper->markAsRead($id);
	}

	/**
	 * Count unread notifications for a user.
	 */
	public function countUnread(string $userId): int {
		return $this->notificationMapper->countUnread($userId);
	}

	/**
	 * Get all notification preferences for a user.
	 *
	 * @return NotifPref[]
	 */
	public function getUserPreferences(string $userId): array {
		return $this->notifPrefMapper->findByUser($userId);
	}

	/**
	 * Update (or create) a notification preference for a user.
	 *
	 * Upsert: finds an existing preference or creates a new one.
	 */
	public function updatePreference(
		string $userId,
		string $eventType,
		string $channel,
		bool $isEnabled,
	): NotifPref {
		$now = new \DateTimeImmutable();

		try {
			$pref = $this->notifPrefMapper->findByUserAndEvent($userId, $eventType);
			$pref->setChannel($channel);
			$pref->setIsEnabled($isEnabled);
			$pref->setUpdatedAt($now);
			return $this->notifPrefMapper->update($pref);
		} catch (DoesNotExistException $e) {
			$pref = new NotifPref();
			$pref->setUserId($userId);
			$pref->setEventType($eventType);
			$pref->setChannel($channel);
			$pref->setIsEnabled($isEnabled);
			$pref->setCreatedAt($now);
			$pref->setUpdatedAt($now);
			return $this->notifPrefMapper->insert($pref);
		}
	}
}
