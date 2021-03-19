<?php

declare(strict_types=1);

namespace App\Taiga\V1\Util;

use App\Taiga\V1\Exception\MaisDeUmaTagEncontradaException;
use App\Taiga\V1\Exception\NenhumaTagEncontradaException;

class TagsParser
{
    private const CONTRATO_PATTERN = '#contrato\s*\d+\/\d+#i';
    private const ORDEM_SERVICO_PATTERN = '#os\s*\d+\/\d+#i';

    /**
     * @param string[] $tags
     */
    public function __construct(private array $tags)
    {
    }

    public function getNomeContrato(): string
    {
        return $this->getTagValueFromRegExp(self::CONTRATO_PATTERN);
    }

    public function getNomeOrdemServico(): string
    {
        return $this->getTagValueFromRegExp(self::ORDEM_SERVICO_PATTERN);
    }

    private function getTagValueFromRegExp(string $pattern): string
    {
        if (false === $filteredTags = preg_grep($pattern, $this->tags)) {
            throw new \LogicException('A função preg_grep falhou. Verifique se o parâmetro $pattern está correto.');
        }

        if (0 === count($filteredTags)) {
            throw new NenhumaTagEncontradaException();
        }

        if (1 < count($filteredTags)) {
            throw new MaisDeUmaTagEncontradaException(array_values($filteredTags));
        }

        // Remoção dos índices
        $filteredTags = array_values($filteredTags);

        return $filteredTags[0];
    }
}
