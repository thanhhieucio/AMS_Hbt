<?php

namespace App\Policies;

class ManufacturerPolicy extends HsbPermissionsPolicy
{
    protected function columnName()
    {
        return 'manufacturers';
    }
}
