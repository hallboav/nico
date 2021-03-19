<?php

declare(strict_types=1);

namespace App\Taiga\V1\Collection;

use App\Taiga\V1\Model\CustomAttributeValueModel;

/**
 * @implements \IteratorAggregate<int, CustomAttributeValueModel>
 */
class CustomAttributeValueCollection implements \IteratorAggregate, \Countable
{
    /**
     * @var CustomAttributeValueModel[]
     */
    private array $customAttributesValues = [];

    /**
     * @param array{'id':int, 'value':string}[] $customAttributesValues
     */
    public function __construct(array $customAttributesValues)
    {
        foreach ($customAttributesValues as $customAttributeValue) {
            $this->add(CustomAttributeValueModel::fromArray($customAttributeValue));
        }
    }

    /**
     * @param array{'attributes_values': array<int|string, string>} $array
     */
    public static function fromArray(array $array): self
    {
        $customAttributesValues = [];
        foreach ($array['attributes_values'] as $idAsString => $value) {
            $customAttributesValues[] = ['id' => intval($idAsString), 'value' => $value];
        }

        return new self($customAttributesValues);
    }

    public function add(CustomAttributeValueModel $customAttributeValue): void
    {
        $this->customAttributesValues[$customAttributeValue->getId()] = $customAttributeValue;
    }

    public function findById(int $id): ?CustomAttributeValueModel
    {
        foreach ($this->customAttributesValues as $customAttributeValue) {
            if ($id === $customAttributeValue->getId()) {
                return $customAttributeValue;
            }
        }

        return null;
    }

    /**
     * @return \ArrayIterator<int, CustomAttributeValueModel>
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->customAttributesValues);
    }

    public function count(): int
    {
        return count($this->customAttributesValues);
    }
}
