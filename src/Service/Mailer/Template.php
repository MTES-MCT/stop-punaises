<?php

namespace App\Service\Mailer;

/**
 * ID related to Sendinblue Provider, you have to login et get the ID
 * https://my.sendinblue.com/camp/lists/template#active.
 */
enum Template: int
{
    case ACCOUNT_ACTIVATION = 15;
    case RESET_PASSWORD = 2;
    case SIGNALEMENT_PROFESSIONAL = 17;
    case SIGNALEMENT_AUTO = 16;
    case SIGNALEMENT_NEW_FOR_PRO = 7;
    case SIGNALEMENT_NEW_ESTIMATION = 12;
    case SIGNALEMENT_NEW_MESSAGE = 6;
    case SIGNALEMENT_NEW_MESSAGE_FOR_PRO = 11;
    case SIGNALEMENT_ESTIMATION_ACCEPTED = 10;
    case SIGNALEMENT_ESTIMATION_REFUSED = 9;
    case SIGNALEMENT_INTERVENTION_CANCELED = 19;
    case SIGNALEMENT_NO_MORE_ENTREPRISES = 5;
    case SIGNALEMENT_SUIVI_TRAITEMENT_PRO = 3;
    case SIGNALEMENT_SUIVI_TRAITEMENT_AUTO = 4;
    case SIGNALEMENT_TRAITEMENT_RESOLVED = 1;
    case SIGNALEMENT_TRAITEMENT_RESOLVED_FOR_PRO = 8;
    case SIGNALEMENT_CLOSED = 13;

    case ADMIN_TOUJOURS_PUNAISES = 14;
    case ADMIN_CONTACT = 20;
}
