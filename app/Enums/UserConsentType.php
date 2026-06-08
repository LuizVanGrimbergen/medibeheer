<?php

namespace App\Enums;

enum UserConsentType: string
{
    case PRIVACY_POLICY = 'privacy_policy';
    case HEALTH_DATA_PROCESSING = 'health_data_processing';
    case TERMS_OF_SERVICE = 'terms_of_service';
}
