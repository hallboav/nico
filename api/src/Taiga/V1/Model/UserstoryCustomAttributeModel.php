<?php

declare(strict_types=1);

namespace App\Taiga\V1\Model;

class UserstoryCustomAttributeModel
{
    public function __construct(private int $id, private string $name)
    {
    }

    /**
     * @param array{
     *     'id': int,
     *     'name': string,
     * } $array
     */
    public static function fromArray(array $array): self
    {
        return new self($array['id'], $array['name']);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
