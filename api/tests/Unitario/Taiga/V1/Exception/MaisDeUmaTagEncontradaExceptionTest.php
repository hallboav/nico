<?php

declare(strict_types=1);

namespace App\Tests\Unitario\Taiga\V1\Exception;

use App\Taiga\V1\Exception\MaisDeUmaTagEncontradaException;
use PHPUnit\Framework\TestCase;

class MaisDeUmaTagEncontradaExceptionTest extends TestCase
{
    public function testException(): void
    {
        $filteredTags = ['foo'];
        $exception = new MaisDeUmaTagEncontradaException($filteredTags);
        $this->assertSame($filteredTags, $exception->getFilteredTags());
    }
}
