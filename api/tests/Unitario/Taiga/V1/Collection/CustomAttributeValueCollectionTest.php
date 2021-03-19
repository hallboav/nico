<?php

declare(strict_types=1);

namespace App\Tests\Unitario\Taiga\V1\Collection;

use App\Taiga\V1\Collection\CustomAttributeValueCollection;
use PHPUnit\Framework\TestCase;

class CustomAttributeValueCollectionTest extends TestCase
{
    /**
     * @dataProvider customAttributesValuesProvider
     */
    public function testGetIterator($customAttributesValues): void
    {
        $customAttributeValueCollection = new CustomAttributeValueCollection($customAttributesValues);
        $this->assertInstanceOf(\Traversable::class, $customAttributeValueCollection->getIterator());
    }

    /**
     * @dataProvider customAttributesValuesProvider
     */
    public function testCount($customAttributesValues): void
    {
        $customAttributeValueCollection = new CustomAttributeValueCollection($customAttributesValues);
        $this->assertEquals(2, count($customAttributeValueCollection));
    }

    public function customAttributesValuesProvider(): array
    {
        $customAttributesValues = [
            ['id' => 123, 'value' => 'foo'],
            ['id' => 456, 'value' => 'bar'],
        ];

        return [
            [$customAttributesValues],
        ];
    }
}
