<?php

declare(strict_types=1);

namespace App\Tests\Unitario\Taiga\V1\Util;

use App\Taiga\V1\Exception\MaisDeUmaTagEncontradaException;
use App\Taiga\V1\Exception\NenhumaTagEncontradaException;
use App\Taiga\V1\Util\TagsParser;
use PHPUnit\Framework\TestCase;

class TagsParserTest extends TestCase
{
    public function testGetNomeContrato(): void
    {
        $tagsParser = new TagsParser(['contrato 03/2021']);
        $this->assertEquals('contrato 03/2021', $tagsParser->getNomeContrato());
    }

    public function testGetNomeOrdemServico(): void
    {
        $tagsParser = new TagsParser(['os 03/2021']);
        $this->assertEquals('os 03/2021', $tagsParser->getNomeOrdemServico());
    }

    public function testGetNomeContratoComMaisDeUmaTag(): void
    {
        $tagsParser = new TagsParser(['contrato 03/2021', 'contrato 04/2021']);

        $this->expectException(MaisDeUmaTagEncontradaException::class);
        $this->expectExceptionMessage('');

        $tagsParser->getNomeContrato();
    }

    public function testGetNomeContratoComTagsDesconhecidas(): void
    {
        $tagsParser = new TagsParser(['abc 03-2021']);

        $this->expectException(NenhumaTagEncontradaException::class);
        $this->expectExceptionMessage('');

        $tagsParser->getNomeContrato();
    }
}
