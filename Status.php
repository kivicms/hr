<?php

namespace NW\WebService\References\Operations\Notification;

enum Status: int
{
    case COMPLETED = 0;
    case PENDING = 1;
    CASE REJECTED = 2;

    public function name(): string
    {
        return match($this)
        {
            self::COMPLETED => 'Completed',
            self::PENDING => 'Pending',
            self::REJECTED => 'Rejected',
        };
    }
}
