<?php

declare(strict_types=1);

namespace App\Taiga\V1\Model;

class UserStoryModel
{
    public function __construct(private int $id, private string $subject, private float $totalPoints)
    {
    }

    /**
     * @param array{
     *     'id': int,
     *     'subject': string,
     *     'total_points': float|null,
     * } $array
     */
    public static function fromArray(array $array): self
    {
        return new self($array['id'], $array['subject'], $array['total_points'] ?? 0.0);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getTotalPoints(): float
    {
        return $this->totalPoints;
    }
}
