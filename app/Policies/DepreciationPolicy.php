<?php

namespace App\Policies;

class DepreciationPolicy extends HsbPermissionsPolicy
{
    protected function columnName()
    {
        return 'depreciations';
    }
}
