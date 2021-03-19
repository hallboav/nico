<?php

declare(strict_types=1);

namespace App\Taiga\V1\Collection;

use App\Taiga\V1\Model\UserStoryModel;

/**
 * @implements \IteratorAggregate<int, UserStoryModel>
 */
class UserStoryCollection implements \IteratorAggregate, \Countable
{
    /**
     * @var UserStoryModel[]
     */
    private array $userStories = [];

    /**
     * @param array{
     *     'id': int,
     *     'subject': string,
     *     'total_points': float|null,
     * }[] $userStories
     */
    public function __construct(array $userStories)
    {
        foreach ($userStories as $userStory) {
            $this->add(UserStoryModel::fromArray($userStory));
        }
    }

    public function add(UserStoryModel $userStory): void
    {
        $this->userStories[$userStory->getId()] = $userStory;
    }

    /**
     * @return \ArrayIterator<int, UserStoryModel>
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->userStories);
    }

    public function count(): int
    {
        return count($this->userStories);
    }
}
