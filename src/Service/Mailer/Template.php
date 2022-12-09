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
    case SIGNALEMENT_PROFESSIONAL = 37;
    case SIGNALEMENT_AUTO = 38;
    case SIGNALEMENT_NEW_FOR_PRO = 39;
    case SIGNALEMENT_NEW_ESTIMATION = 43;
    case SIGNALEMENT_NEW_MESSAGE = 48;
    case SIGNALEMENT_NEW_MESSAGE_FOR_PRO = 49;
    case SIGNALEMENT_TRAITEMENT_RESOLVED = 44;
    case SIGNALEMENT_SUIVI_TRAITEMENT_PRO = 45;
    case SIGNALEMENT_SUIVI_TRAITEMENT_AUTO = 46;
    case SIGNALEMENT_NO_MORE_ENTREPRISES = 47;
}
