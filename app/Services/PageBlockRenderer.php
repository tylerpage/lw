<?php

namespace App\Services;

use App\PageBlocks\Block;
use App\PageBlocks\BlockRegistry;
use Illuminate\Support\Facades\View;

class PageBlockRenderer
{
    public function render(array $blocks): string
    {
        $html = '';

        foreach ($blocks as $blockData) {
            if (! ($blockData['enabled'] ?? true)) {
                continue;
            }

            try {
                $block = BlockRegistry::fromArray($blockData);
                $html .= $this->renderBlock($block);
            } catch (\Throwable) {
                continue;
            }
        }

        return $html;
    }

    public function renderBlock(Block $block): string
    {
        $view = 'components.blocks.'.$block::type();

        if (! View::exists($view)) {
            return '';
        }

        return View::make($view, ['block' => $block->toArray()])->render();
    }
}
