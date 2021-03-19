<?php

declare(strict_types=1);

namespace App\Taiga\V1\Collection;

use App\Taiga\V1\Model\UserstoryCustomAttributeModel;

/**
 * @implements \IteratorAggregate<int, UserstoryCustomAttributeModel>
 */
class UserstoryCustomAttributeCollection implements \IteratorAggregate, \Countable
{
    /**
     * @var UserstoryCustomAttributeModel[]
     */
    private array $userstoryCustomAttributes = [];

    /**
     * @param array{'id':int, 'name':string}[] $userstoryCustomAttributes
     */
    public function __construct(array $userstoryCustomAttributes)
    {
        foreach ($userstoryCustomAttributes as $userstoryCustomAttribute) {
            $this->add(UserstoryCustomAttributeModel::fromArray($userstoryCustomAttribute));
        }
    }

    /**
     * @param array{
     *     'id': int,
     *     'name': string,
     * }[] $array
     */
    public static function fromArray(array $array): self
    {
        $callback = fn (array $userstoryCustomAttribute): array => [
            'id' => $userstoryCustomAttribute['id'],
            'name' => $userstoryCustomAttribute['name'],
        ];

        $userstoryCustomAttributes = array_map($callback, $array);

        return new self($userstoryCustomAttributes);
    }

    public function add(UserstoryCustomAttributeModel $userstoryCustomAttribute): void
    {
        $this->userstoryCustomAttributes[$userstoryCustomAttribute->getId()] = $userstoryCustomAttribute;
    }

    public function findByRegExp(string $pattern): ?UserstoryCustomAttributeModel
    {
        foreach ($this->userstoryCustomAttributes as $userstoryCustomAttribute) {
            if (preg_match($pattern, $userstoryCustomAttribute->getName())) {
                return $userstoryCustomAttribute;
            }
        }

        return null;
    }

    /**
     * @return \ArrayIterator<int, UserstoryCustomAttributeModel>
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->userstoryCustomAttributes);
    }

    public function count(): int
    {
        return count($this->userstoryCustomAttributes);
    }
}
