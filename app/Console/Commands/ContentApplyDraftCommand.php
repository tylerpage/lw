<?php

namespace App\Console\Commands;

use App\ContentAssistant\Services\ContentAssistantOrchestrator;
use App\Enums\ContentProposalStatus;
use App\Models\ContentProposal;
use App\Models\User;
use Illuminate\Console\Command;

class ContentApplyDraftCommand extends Command
{
    protected $signature = 'content:apply-draft {proposal} {--user=1}';

    protected $description = 'Apply a validated proposal as a CMS draft';

    public function handle(ContentAssistantOrchestrator $orchestrator): int
    {
        $proposal = ContentProposal::query()->findOrFail($this->argument('proposal'));
        $user = User::query()->findOrFail((int) $this->option('user'));

        if ($proposal->status !== ContentProposalStatus::Validated) {
            $result = $orchestrator->validateProposal($proposal, $user);
            if (! $result['valid']) {
                $this->error('Proposal is not valid.');

                return self::FAILURE;
            }
            $proposal->refresh();
        }

        $applied = $orchestrator->applyDraft($proposal, $user);

        $this->info("Draft saved for proposal {$applied->id} ({$applied->status->value}).");

        return self::SUCCESS;
    }
}
