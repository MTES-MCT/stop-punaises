<?php

namespace App\Entity\Enum;

enum HistoryEntryEvent: string
{
    case LOGIN = 'LOGIN';

    case CREATE = 'CREATE';

    case UPDATE = 'UPDATE';

    case DELETE = 'DELETE';
}
