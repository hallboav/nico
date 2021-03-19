<?php

declare(strict_types=1);

namespace App\Taiga\V1;

use App\Taiga\V1\Collection\CustomAttributeValueCollection;
use App\Taiga\V1\Collection\UserstoryCustomAttributeCollection;
use App\Taiga\V1\Model\MilestoneModel;
use App\Taiga\V1\Model\ProjectModel;

interface TaigaInterface
{
    public function getProjectById(int $id): ProjectModel;

    public function getMilestoneById(int $id): MilestoneModel;

    public function getCustomAttributesValuesByUserStoryId(int $id): CustomAttributeValueCollection;

    public function getUserstoryCustomAttributeByProjectId(int $id): UserstoryCustomAttributeCollection;
}
