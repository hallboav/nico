<?php

declare(strict_types=1);

namespace App\Taiga\V1\Model;

class CustomAttributeValueModel
{
    public function __construct(private int $id, private string $value)
    {
    }

    /**
     * @param array{'id':int, 'value':string} $array
     */
    public static function fromArray(array $array): self
    {
        return new self($array['id'], $array['value']);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
