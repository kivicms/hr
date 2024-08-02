<?php

namespace NW\WebService\References\Operations\Notification;

use NW\WebService\References\Operations\Notification\Enums\NotificationEvent;

/**
 * Class NotificationManager
 * Класс заглушка
 * The NotificationManager class is responsible for sending notifications.
 */
class NotificationManager
{
    /**
     * @param int $resellerId
     * @param int $clientId
     * @param NotificationEvent $event
     * @param int $difference
     * @param array $templateData
     * @param string $error
     * @return bool
     */
    public static function send(int $resellerId, int $clientId, NotificationEvent $event, int $difference, array $templateData, string $error): bool
    {
        return rand(1, 10) > 5;
    }
}
