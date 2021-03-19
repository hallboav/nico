<?php

declare(strict_types=1);

namespace App\Tests\Unitario\EntityListener;

use App\Entity\Sprint;
use App\EntityListener\SprintEntityListener;
use App\Taiga\V1\MockTaiga;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Validator\Validation;

class SprintEntityListenerTest extends TestCase
{
    public function testValidator(): void
    {
        $sprint = new Sprint();
        $sprint->setIsFechada(true);

        $validator = Validation::createValidatorBuilder()
            ->enableAnnotationMapping(true)
            ->addDefaultDoctrineAnnotationReader()
            ->getValidator();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(<<<EXCEPTION
projeto: This value should not be blank.
taigaId: This value should not be blank.
nome: This value should not be blank.
iniciadaEm: This value should not be blank.
finalizadaEm: This value should not be blank.
EXCEPTION);

        $sprintEntityListener = new SprintEntityListener(new MockTaiga(), $validator, new NullLogger());
        $sprintEntityListener->onPrePersistOrUpdate($sprint);
    }
}
