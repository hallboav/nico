<?php

declare(strict_types=1);

namespace App\Taiga\V1\Model;

use App\Taiga\V1\Collection\UserStoryCollection;

class MilestoneModel
{
    public function __construct(
        private int $id,
        private float $totalPoints,
        private UserStoryCollection $userStories
    ) {
    }

    /**
     * @param array{
     *     'id': int,
     *     'total_points': float|null,
     *     'user_stories': array{
     *         'id': int,
     *         'subject': string,
     *         'total_points': float|null,
     *     }[],
     * } $array
     */
    public static function fromArray(array $array): self
    {
        $userStories = array_map(fn (array $userStory): array => [
            'id' => $userStory['id'],
            'subject' => $userStory['subject'],
            'total_points' => $userStory['total_points'],
        ], $array['user_stories']);

        return new self(
            $array['id'],
            $array['total_points'] ?? 0.0,
            // $array['closed_points'] ?? 0.0,
            new UserStoryCollection($userStories)
        );
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTotalPoints(): float
    {
        return $this->totalPoints;
    }

    public function getUserStories(): UserStoryCollection
    {
        return $this->userStories;
    }
}
