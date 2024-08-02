<?php

namespace NW\WebService\References\Operations\Notification;

function getResellerEmailFrom(): string
{
    return 'contractor@example.com';
}

function getEmailsByPermit($resellerId, $event): array
{
    // fakes the method
    return ['someemeil@example.com', 'someemeil2@example.com'];
}

// Это заглушка для метода перевода
function __(string $method, array|null $data = null, int $resellerId): int
{
    return rand(1, 100);
}
