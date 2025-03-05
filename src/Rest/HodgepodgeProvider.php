<?php

declare(strict_types=1);

namespace Dbp\Relay\NexusBundle\Rest;

use Dbp\Relay\CoreBundle\Rest\AbstractDataProvider;
use Dbp\Relay\NexusBundle\Entity\Hodgepodge;
use Dbp\Relay\NexusBundle\Service\HodgepodgeService;

/**
 * @extends AbstractDataProvider<Hodgepodge>
 */
class HodgepodgeProvider extends AbstractDataProvider
{
    private HodgepodgeService $hodgepodgeService;

    public function __construct(HodgepodgeService $hodgepodgeService)
    {
        parent::__construct();
        $this->hodgepodgeService = $hodgepodgeService;
    }

    protected function getItemById(string $id, array $filters = [], array $options = []): ?Hodgepodge
    {
        return $this->hodgepodgeService->getHodgepodge($id, $filters, $options);
    }

    /**
     * @return Hodgepodge[]
     */
    protected function getPage(int $currentPageNumber, int $maxNumItemsPerPage, array $filters = [], array $options = []): array
    {
        return $this->hodgepodgeService->getHodgepodges($currentPageNumber, $maxNumItemsPerPage, $filters, $options);
    }
}
