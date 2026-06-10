<?php

namespace App\Enums;

use App\Enums\Concerns\EncryptEnum;
use Illuminate\Contracts\Database\Eloquent\Castable;

enum UserConsentType: string implements Castable
{
    use EncryptEnum;

    case PRIVACY_POLICY = 'privacy_policy';
    case HEALTH_DATA_PROCESSING = 'health_data_processing';
    case TERMS_OF_SERVICE = 'terms_of_service';
}
