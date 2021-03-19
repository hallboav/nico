<?php

declare(strict_types=1);

namespace App\Taiga\V1\Model;

use App\Taiga\V1\Util\TagsParser;

class ProjectModel
{
    /**
     * @param string[] $tags
     */
    public function __construct(
        private int $id,
        private string $name,
        private string $description,
        private array $tags
    ) {
    }

    /**
     * @param array{
     *     'id': int,
     *     'name': string,
     *     'description': string,
     *     'tags': string[],
     * } $array
     */
    public static function fromArray(array $array): self
    {
        return new self(
            $array['id'],
            $array['name'],
            $array['description'],
            $array['tags']
        );
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return string[]
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    public function createTagsParser(): TagsParser
    {
        return new TagsParser($this->getTags());
    }
}
