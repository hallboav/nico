<?php

declare(strict_types=1);

namespace App\Tests\Unitario\Taiga\V1\Collection;

use App\Taiga\V1\Collection\UserstoryCustomAttributeCollection;
use PHPUnit\Framework\TestCase;

class UserstoryCustomAttributeCollectionTest extends TestCase
{
    /**
     * @dataProvider customAttributesValuesProvider
     */
    public function testGetIterator($customAttributesValues): void
    {
        $customAttributeValueCollection = new UserstoryCustomAttributeCollection($customAttributesValues);
        $this->assertInstanceOf(\Traversable::class, $customAttributeValueCollection->getIterator());
    }

    /**
     * @dataProvider customAttributesValuesProvider
     */
    public function testCount($customAttributesValues): void
    {
        $customAttributeValueCollection = new UserstoryCustomAttributeCollection($customAttributesValues);
        $this->assertEquals(2, count($customAttributeValueCollection));
    }

    public function customAttributesValuesProvider(): array
    {
        $customAttributesValues = [
            ['id' => 123, 'name' => 'foo'],
            ['id' => 456, 'name' => 'bar'],
        ];

        return [
            [$customAttributesValues],
        ];
    }
}
