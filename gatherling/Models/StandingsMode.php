<?php

declare(strict_types=1);

namespace Gatherling\Models;

enum StandingsMode
{
    case STANDINGS;
    case NEXT_UNPAIRED;
    case SEEDED;
    case ACTIVE_STANDINGS;
}
