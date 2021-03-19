<?php

declare(strict_types=1);

namespace App\Taiga\V1\Exception;

class MaisDeUmaTagEncontradaException extends \RuntimeException
{
    /**
     * @param string[] $filteredTags
     */
    public function __construct(private array $filteredTags)
    {
        parent::__construct();
    }

    /**
     * @return string[]
     */
    public function getFilteredTags(): array
    {
        return $this->filteredTags;
    }
}
