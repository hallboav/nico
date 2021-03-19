<?php

declare(strict_types=1);

namespace App\Taiga\V1\Endpoint;

use App\Taiga\V1\Collection\CustomAttributeValueCollection;

interface UserstoriesEndpointInterface
{
    public function getCustomAttributesValuesByUserStoryId(int $id): CustomAttributeValueCollection;
}
