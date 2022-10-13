<?php

namespace App\Service\Mailer;

enum Template: int
{
    case ACCOUNT_ACTIVATION = 24;
    case RESET_PASSWORD = 27;
}
