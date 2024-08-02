<?php

namespace NW\WebService\References\Operations\Notification;

use NW\WebService\References\Operations\Notification\Enums\NotificationEvent;

/**
 * Class MessagesClient
 * Класс заглушка для отправки уведомлений
 * Provides functionality for sending messages.
 */
class MessagesClient
{
    // Метод заглушка дает случайный ответ
    public static function sendMessage(array $data, int $resellerId, NotificationEvent $event, int $clientId = null, int $difference = null): string
    {
        return rand(1, 10) > 5 ? 'Ошибка' : '';
    }
}
