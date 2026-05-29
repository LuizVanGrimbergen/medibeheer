<?php

namespace App\Enums;

enum AppointmentTransportStatus: string
{
    case REQUESTED = 'requested';
    case ACCEPTED = 'accepted';
    case DECLINED = 'declined';

}
