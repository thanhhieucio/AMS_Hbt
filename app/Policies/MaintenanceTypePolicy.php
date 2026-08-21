<?php

namespace App\Policies;

class MaintenanceTypePolicy extends HsbPermissionsPolicy
{
    protected function columnName()
    {
        return 'maintenances';
    }
}
