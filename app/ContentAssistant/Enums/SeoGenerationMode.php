<?php

namespace App\ContentAssistant\Enums;

enum SeoGenerationMode: string
{
    case FromContent = 'from_content';
    case MetaOnly = 'meta_only';
    case OpenGraphOnly = 'open_graph_only';
    case ImproveExisting = 'improve_existing';
}
