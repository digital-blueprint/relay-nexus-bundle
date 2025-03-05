<?php

declare(strict_types=1);

namespace Dbp\Relay\NexusBundle\Rest;

use Dbp\Relay\CoreBundle\Rest\AbstractDataProcessor;
use Dbp\Relay\NexusBundle\Entity\Hodgepodge;
use Dbp\Relay\NexusBundle\Service\HodgepodgeService;

class HodgepodgeProcessor extends AbstractDataProcessor
{
    private HodgepodgeService $hodgepodgeService;

    public function __construct(HodgepodgeService $hodgepodgeService)
    {
        parent::__construct();
        $this->hodgepodgeService = $hodgepodgeService;
    }

    protected function isCurrentUserGrantedOperationAccess(int $operation): bool
    {
        return true; // TODO ???
    }

    protected function addItem(mixed $data, array $filters): Hodgepodge
    {
        assert($data instanceof Hodgepodge);

        $data->setIdentifier('42');

        return $this->hodgepodgeService->addHodgepodge($data);
    }

    protected function removeItem($identifier, $data, array $filters): void
    {
        assert($data instanceof Hodgepodge);

        $this->hodgepodgeService->removeHodgepodge($data);
    }
}
