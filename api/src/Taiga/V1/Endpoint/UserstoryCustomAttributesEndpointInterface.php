<?php

declare(strict_types=1);

namespace App\Taiga\V1\Endpoint;

use App\Taiga\V1\Collection\UserstoryCustomAttributeCollection;

interface UserstoryCustomAttributesEndpointInterface
{
    public function getByProjectId(int $id): UserstoryCustomAttributeCollection;
}
