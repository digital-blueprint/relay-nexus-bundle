<?php

declare(strict_types=1);

namespace Dbp\Relay\NexusBundle\Authorization;

use Dbp\Relay\CoreBundle\Authorization\AbstractAuthorizationService;
use Dbp\Relay\NexusBundle\DependencyInjection\Configuration;

class AuthorizationService extends AbstractAuthorizationService
{
    /**
     * Check if the user can access the application at all.
     */
    public function checkCanUse(): void
    {
        $this->denyAccessUnlessIsGranted(Configuration::ROLE_USER);
    }

    /**
     * Returns if the user can use the application at all.
     */
    public function getCanUse(): bool
    {
        return $this->isGranted(Configuration::ROLE_USER);
    }

    public function validateConfiguration()
    {
        $this->getCanUse();
    }
}
