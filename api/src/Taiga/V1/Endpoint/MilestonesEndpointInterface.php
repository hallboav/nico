<?php

declare(strict_types=1);

namespace App\Taiga\V1\Endpoint;

use App\Taiga\V1\Model\MilestoneModel;

interface MilestonesEndpointInterface
{
    public function getById(int $id): MilestoneModel;
}
