<?php

namespace App\Policies;

class PredefinedKitPolicy extends HsbPermissionsPolicy
{
    protected function columnName()
    {
        return 'kits';
    }
}
