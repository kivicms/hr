<?php

namespace NW\WebService\References\Operations\Notification;

/**
 * Class ReferencesOperation
 *
 * Это абстрактный класс, представляющий общую операцию справки.
 * Подклассы этого класса должны реализовать метод doOperation()
 * для определения конкретной операции, которую следует выполнить.
 */
abstract class ReferencesOperation
{
    abstract public function doOperation(): array;

    public function getRequest($pName): array
    {
        return $_REQUEST[$pName];
    }
}
