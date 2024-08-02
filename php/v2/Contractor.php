<?php

namespace NW\WebService\References\Operations\Notification;

/**
 * Class Contractor
 *
 * Представляет сущность подрядчика.
 */
class Contractor
{

    public const int TYPE_CUSTOMER = 0; // добавил видимость public
    public int $id;
    public int $type;
    public string $name;
    public Seller $seller;


    /**
     * Returns an instance of the class based on the reseller id.
     *
     * @param int $resellerId The id of the reseller.
     * @return self The instance of the class.
     */
    public static function getById(int $resellerId): self
    {
        return new self($resellerId); // fakes the getById method
    }

    /**
     * Returns the full name of the object by concatenating the name and id properties.
     *
     * @return string The full name of the object.
     */
    public function getFullName(): string
    {
        return $this->name . ' ' . $this->id;
    }
}
