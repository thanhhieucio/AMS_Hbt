<?php

namespace App\Policies;

class StatuslabelPolicy extends HsbPermissionsPolicy
{
    protected function columnName()
    {
        return 'statuslabels';
    }
}
