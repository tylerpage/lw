<?php

namespace App\Filament\Forms;

enum ContentFormType: string
{
    case Page = 'page';
    case Post = 'post';
    case Project = 'project';
}
