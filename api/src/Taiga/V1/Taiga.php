<?php

declare(strict_types=1);

namespace App\Taiga\V1;

use App\Taiga\V1\Collection\CustomAttributeValueCollection;
use App\Taiga\V1\Collection\UserstoryCustomAttributeCollection;
use App\Taiga\V1\Endpoint\MilestonesEndpointInterface;
use App\Taiga\V1\Endpoint\ProjectsEndpointInterface;
use App\Taiga\V1\Endpoint\UserstoriesEndpointInterface;
use App\Taiga\V1\Endpoint\UserstoryCustomAttributesEndpointInterface;
use App\Taiga\V1\Model\MilestoneModel;
use App\Taiga\V1\Model\ProjectModel;

class Taiga implements TaigaInterface
{
    public function __construct(
        private ProjectsEndpointInterface $projectsEndpoint,
        private MilestonesEndpointInterface $milestonesEndpoint,
        private UserstoriesEndpointInterface $userstoriesEndpoint,
        private UserstoryCustomAttributesEndpointInterface $userstoryCustomAttributesEndpoint
    ) {
    }

    public function getProjectById(int $id): ProjectModel
    {
        return $this->projectsEndpoint->getById($id);
    }

    public function getMilestoneById(int $id): MilestoneModel
    {
        return $this->milestonesEndpoint->getById($id);
    }

    public function getCustomAttributesValuesByUserStoryId(int $id): CustomAttributeValueCollection
    {
        return $this->userstoriesEndpoint->getCustomAttributesValuesByUserStoryId($id);
    }

    public function getUserstoryCustomAttributeByProjectId(int $id): UserstoryCustomAttributeCollection
    {
        return $this->userstoryCustomAttributesEndpoint->getByProjectId($id);
    }
}
