<?php

declare(strict_types=1);

namespace Yokai\Batch\Storage;

/**
 * Defines possible sort directions for {@see QueryBuilder::sort}.
 */
enum SortDirection: string
{
    case StartAsc  = 'start_asc';
    case StartDesc = 'start_desc';
    case EndAsc    = 'end_asc';
    case EndDesc   = 'end_desc';
}
