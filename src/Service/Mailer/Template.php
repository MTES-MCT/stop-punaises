<?php

namespace App\Service\Mailer;

/**
 * ID related to Sendinblue Provider, you have to login et get the ID
 * https://my.sendinblue.com/camp/lists/template#active.
 */
enum Template: int
{
    case ACCOUNT_ACTIVATION = 24;
    case RESET_PASSWORD = 27;
}
