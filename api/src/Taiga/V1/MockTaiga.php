<?php

declare(strict_types=1);

namespace App\Taiga\V1;

use App\Taiga\V1\Collection\CustomAttributeValueCollection;
use App\Taiga\V1\Collection\UserstoryCustomAttributeCollection;
use App\Taiga\V1\Model\MilestoneModel;
use App\Taiga\V1\Model\ProjectModel;

class MockTaiga implements TaigaInterface
{
    public function getProjectById(int $id): ProjectModel
    {
        return ProjectModel::fromArray([
            'id' => 39,
            'name' => 'SAND BOX',
            'description' => 'Foo bar',
            'tags' => ['os 01/2021'],
        ]);
    }

    public function getMilestoneById(int $id): MilestoneModel
    {
        return MilestoneModel::fromArray([
            'id' => 115,
            'total_points' => 5.5,
            'user_stories' => [
                [
                    'id' => 1,
                    'subject' => 'foo',
                    'total_points' => 3.0,
                ],
                [
                    'id' => 2,
                    'subject' => 'bar',
                    'total_points' => 2.5,
                ],
            ],
        ]);
    }

    public function getCustomAttributesValuesByUserStoryId(int $id): CustomAttributeValueCollection
    {
        return CustomAttributeValueCollection::fromArray([
            'attributes_values' => [
                '70' => 1 === $id ? 'SIM' : 'NÃO',
                '71' => 'SIM',
            ],
        ]);
    }

    public function getUserstoryCustomAttributeByProjectId(int $id): UserstoryCustomAttributeCollection
    {
        return UserstoryCustomAttributeCollection::fromArray([
            [
                'id' => 70,
                'name' => 'Item de backlog falhou?',
            ],
            [
                'id' => 71,
                'name' => 'Finalizado na Sprint?',
            ],
        ]);
    }
}
