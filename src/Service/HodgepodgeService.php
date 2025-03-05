<?php

declare(strict_types=1);

namespace Dbp\Relay\NexusBundle\Service;

use Dbp\Relay\NexusBundle\Entity\Hodgepodge;

class HodgepodgeService
{
    public function setConfig(array $config): void
    {
    }

    public function getHodgepodge(string $identifier, array $filters = [], array $options = []): ?Hodgepodge
    {
        return null;
    }

    /**
     * @return Hodgepodge[]
     */
    public function getHodgepodges(int $currentPageNumber, int $maxNumItemsPerPage, array $filters, array $options): array
    {
        return [];
    }

    public function addHodgepodge(Hodgepodge $data): Hodgepodge
    {
        return $data;
    }

    public function removeHodgepodge(Hodgepodge $data): void
    {
    }
}
