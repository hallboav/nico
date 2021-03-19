<?php

declare(strict_types=1);

namespace App\Tests\Unitario\Taiga\V1;

use App\Taiga\V1\Collection\CustomAttributeValueCollection;
use App\Taiga\V1\Collection\UserstoryCustomAttributeCollection;
use App\Taiga\V1\Endpoint\MilestonesEndpointInterface;
use App\Taiga\V1\Endpoint\ProjectsEndpointInterface;
use App\Taiga\V1\Endpoint\UserstoriesEndpointInterface;
use App\Taiga\V1\Endpoint\UserstoryCustomAttributesEndpointInterface;
use App\Taiga\V1\Model\MilestoneModel;
use App\Taiga\V1\Model\ProjectModel;
use App\Taiga\V1\Taiga;
use PHPUnit\Framework\TestCase;

class TaigaTest extends TestCase
{
    public function testGetProjectById(): void
    {
        $projectModelMock = $this->createMock(ProjectModel::class);

        $projectsEndpointMock = $this->createMock(ProjectsEndpointInterface::class);
        $projectsEndpointMock->expects($this->once())
            ->method('getById')
            ->with(123)
            ->willReturn($projectModelMock);

        $taiga = new Taiga(
            $projectsEndpointMock,
            $this->createMock(MilestonesEndpointInterface::class),
            $this->createMock(UserstoriesEndpointInterface::class),
            $this->createMock(UserstoryCustomAttributesEndpointInterface::class),
        );

        $actual = $taiga->getProjectById(123);
        $this->assertSame($projectModelMock, $actual);
    }

    public function testGetMilestoneById(): void
    {
        $milestoneModelMock = $this->createMock(MilestoneModel::class);

        $milestonesEndpointMock = $this->createMock(MilestonesEndpointInterface::class);
        $milestonesEndpointMock->expects($this->once())
            ->method('getById')
            ->with(123)
            ->willReturn($milestoneModelMock);

        $taiga = new Taiga(
            $this->createMock(ProjectsEndpointInterface::class),
            $milestonesEndpointMock,
            $this->createMock(UserstoriesEndpointInterface::class),
            $this->createMock(UserstoryCustomAttributesEndpointInterface::class),
        );

        $actual = $taiga->getMilestoneById(123);
        $this->assertSame($milestoneModelMock, $actual);
    }

    public function testGetCustomAttributesValuesByUserStoryId(): void
    {
        $customAttributeValueCollectionMock = $this->createMock(CustomAttributeValueCollection::class);

        $userstoriesEndpointMock = $this->createMock(UserstoriesEndpointInterface::class);
        $userstoriesEndpointMock->expects($this->once())
            ->method('getCustomAttributesValuesByUserStoryId')
            ->with(123)
            ->willReturn($customAttributeValueCollectionMock);

        $taiga = new Taiga(
            $this->createMock(ProjectsEndpointInterface::class),
            $this->createMock(MilestonesEndpointInterface::class),
            $userstoriesEndpointMock,
            $this->createMock(UserstoryCustomAttributesEndpointInterface::class),
        );

        $actual = $taiga->getCustomAttributesValuesByUserStoryId(123);
        $this->assertSame($customAttributeValueCollectionMock, $actual);
    }

    public function testGetUserstoryCustomAttributeByProjectId(): void
    {
        $userstoryCustomAttributeCollectionMock = $this->createMock(UserstoryCustomAttributeCollection::class);

        $userstoryCustomAttributesEndpointMock = $this->createMock(UserstoryCustomAttributesEndpointInterface::class);
        $userstoryCustomAttributesEndpointMock->expects($this->once())
            ->method('getByProjectId')
            ->with(123)
            ->willReturn($userstoryCustomAttributeCollectionMock);

        $taiga = new Taiga(
            $this->createMock(ProjectsEndpointInterface::class),
            $this->createMock(MilestonesEndpointInterface::class),
            $this->createMock(UserstoriesEndpointInterface::class),
            $userstoryCustomAttributesEndpointMock,
        );

        $actual = $taiga->getUserstoryCustomAttributeByProjectId(123);
        $this->assertSame($userstoryCustomAttributeCollectionMock, $actual);
    }
}
