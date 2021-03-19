<?php

declare(strict_types=1);

namespace App\Taiga\V1\Endpoint;

use App\Taiga\V1\Model\ProjectModel;

interface ProjectsEndpointInterface
{
    public function getById(int $id): ProjectModel;
}
