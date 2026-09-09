<?php

namespace App\ContentAssistant\Enums;

enum ContentImportMode: string
{
    case Replace = 'replace';
    case Append = 'append';
    case BlocksOnly = 'blocks_only';
}
