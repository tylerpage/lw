<?php

namespace App\ContentAssistant\Contracts;

use App\ContentAssistant\DTO\ContentAssistantRequest;
use App\ContentAssistant\DTO\ContentProposalData;

interface ContentAssistantGateway
{
    public function propose(ContentAssistantRequest $request): ContentProposalData;
}
