# Conversational Content Assistant — Product Requirements

## 1. Product summary

Build a conversational content assistant inside the Laravel/Filament administration area for Lindsey Wegmann's portfolio website. The assistant lets an authorized editor describe a content change in plain language, review the proposed changes, preview them on the actual site, revise them through conversation, and explicitly approve publication.

The assistant is for content operations, not unrestricted software development. It must use the application's existing content models, page-builder schemas, validation, permissions, revisions, and publishing workflow. It must never receive general shell access, arbitrary SQL access, or permission to edit production code.

Cursor remains the tool for code, new components, layout changes, and integrations. The in-site assistant handles normal page, blog, case-study, SEO, and navigation content changes.

## 2. Product goals

1. Allow Lindsey or an approved editor to update the site without understanding Laravel, Filament, Git, JSON, or deployment workflows.
2. Turn conversational requests into valid structured CMS changes.
3. Keep every AI-created change reviewable, attributable, reversible, and unpublished by default.
4. Preserve verified biographical facts and prevent fabricated clients, metrics, testimonials, roles, dates, or credentials.
5. Reduce routine developer involvement while maintaining design, accessibility, SEO, and security standards.

## 3. Non-goals

The first release will not:

- Modify PHP, Blade, JavaScript, CSS, migrations, configuration, or infrastructure
- Create new page-builder block types
- Run arbitrary Artisan or shell commands supplied by a user
- Query or modify the database with generated SQL
- Publish without a separate explicit approval action
- Generate claims, statistics, testimonials, employment history, or case-study results without approved sources
- Replace the normal Filament editors
- Act as a general-purpose chatbot for public site visitors
- Automatically open Cursor Cloud Agents for routine content changes

## 4. Users and roles

### Super admin

- Configure the AI provider and assistant settings
- Manage approved sources and global content guardrails
- Use, approve, publish, reject, and roll back content changes
- View all conversations and audit history

### Editor

- Start conversations
- Request supported content changes
- Review diffs and previews
- Revise, approve, schedule, publish, reject, and roll back changes within existing CMS permissions

### Author

- Draft and revise their own blog posts
- View previews
- Submit changes for editor approval
- Cannot publish, change pages/global settings, or access other authors' private drafts

The assistant must call Laravel authorization policies for every read and mutation. Hiding an action in the interface is not authorization.

## 5. Primary user stories

### Update existing page content

> As an editor, I can say, “Make the homepage hero focus more on connecting marketing and engineering, but keep both CTAs,” and receive a proposed change to the existing hero block.

### Draft a blog post

> As an author, I can provide a topic and notes, receive an outline, refine it, and create a structured draft post without publishing it.

### Create a case-study draft

> As an editor, I can provide approved project notes and have the assistant organize them into the existing case-study structure while marking unsupported outcomes as missing.

### Update SEO metadata

> As an editor, I can ask for improved titles and descriptions for selected pages, review character lengths and diffs, and save approved metadata as drafts.

### Revise through conversation

> As an editor, I can say, “Keep the first sentence, make the rest less formal, and remove the Taylor Swift reference,” and the assistant updates only the proposed draft.

### Preview and publish

> As an editor, I can preview the exact proposed revision on the real templates, approve it, and then publish or schedule it through a distinct confirmation step.

### Roll back

> As an editor, I can restore the prior published revision if a change is incorrect.

## 6. Supported actions for version one

| Content area | Supported actions |
| --- | --- |
| Pages | Read, rewrite fields, add/reorder/remove supported blocks, save draft |
| Blog posts | Outline, create draft, rewrite, categorize, suggest related posts, schedule |
| Case studies | Create draft from approved notes, rewrite, organize blocks, tag capabilities |
| SEO | Draft title, meta description, canonical suggestion, social title/description |
| Navigation | Rename, reorder, add link to existing published content, remove link |
| Global content | Update approved bio, status line, CTA labels, footer copy |
| Media | Select existing media and suggest alt text; no generated upload in v1 |
| Redirects | Suggest a redirect when a slug changes; require explicit approval |

The assistant should refuse unsupported code/layout requests and provide a clear handoff message: “This requires a developer change in Cursor.”

## 7. Conversation experience

Add an `AI Content Assistant` page to Filament.

### Layout

- Left sidebar: conversations with title, content target, status, and last activity
- Main panel: conversational thread
- Context header: selected page/post/project, current publish status, last editor
- Change panel: structured proposal, validation status, and field/block diff
- Action bar: revise, discard, save draft, preview, submit for approval, approve, schedule, publish

### Starting a conversation

The user can:

- Select an existing content record first and then ask for a change
- Start with a request and let the assistant return a short list of matching records
- Choose `New blog post` or `New case study`

Never silently select a record when multiple plausible matches exist.

### Assistant responses

Each substantive response should distinguish:

- What the assistant understood
- What it proposes to change
- Information it still needs
- Any claims it could not verify
- The next available action

Avoid exposing raw prompts, model reasoning, internal IDs, or full JSON in the normal editor interface.

## 8. Change lifecycle

Use these states:

```text
collecting_context
proposed
needs_revision
validated
draft_saved
awaiting_approval
approved
scheduled
published
rejected
failed
rolled_back
```

Required transition rules:

- AI generation can create only `proposed` changes.
- A proposal must pass schema, business-rule, policy, and safety validation before `validated`.
- Saving creates a CMS draft/revision, never a public mutation.
- Authors submit for approval; they cannot approve their own restricted changes.
- `Approve` does not automatically mean `Publish`.
- Publishing requires a distinct action that shows target, affected URLs, approver, and timing.
- Slug changes show the redirect that will be created.
- A publish failure preserves the prior public revision.
- Rollback creates a new revision pointing to the restored state; do not erase history.

## 9. Required confirmation behavior

No conversational phrase alone may publish content. Even if the user types “publish it,” the interface must present a server-generated confirmation modal.

The modal must show:

- Record title and type
- Current and proposed status
- Affected public URL(s)
- Summary of fields and blocks changed
- Scheduled time and site timezone, if applicable
- Redirect changes, if applicable
- Named approver/publisher

The final publish request must use a short-lived, single-use server token bound to the user, proposal, revision hash, and action. If content changes after approval, require approval again.

## 10. Content grounding and factual safety

### Approved source registry

Create an admin-managed source registry containing:

- Source title
- Source type: resume, LinkedIn export, approved bio, project brief, testimonial approval, brand guide, other
- File or structured content reference
- Approval status
- Approved by and approved date
- Applicable content areas
- Optional expiration/review date

Initial approved foundations include Lindsey's supplied Irish Titan and Surdyk's experience and the portfolio requirements document. Final public wording still requires editorial approval.

### Claim policy

The assistant must not invent or infer:

- Revenue, conversion, traffic, ROAS, or growth figures
- Client names or confidential project details
- Testimonials or endorsements
- Awards, education, or certifications
- Employment dates or job titles
- Team size, budget, or project scope
- First-person anecdotes not present in approved notes

If the requested draft requires missing evidence, insert an explicit internal placeholder such as `[RESULT METRIC NEEDED]`. Never render internal placeholders on a published page; publishing validation must block them.

### Voice context

Include a versioned assistant instruction profile with:

- Strategic but plainspoken
- Warm, direct, and useful
- First-person voice for Lindsey's content
- Concrete language over buzzwords
- Subtle personality; Taylor Swift and golden-retriever references are optional, removable, and never required for understanding
- No copyrighted lyrics or implication of celebrity endorsement

## 11. Structured change contract

The language model returns a proposed change object validated against a strict JSON schema. It never sends arbitrary model-generated HTML to storage.

Example:

```json
{
  "target": {
    "type": "page",
    "id": 1,
    "expected_revision": 14
  },
  "summary": "Refocus the homepage hero on cross-functional strategy.",
  "operations": [
    {
      "op": "replace_block_fields",
      "block_id": "home-hero",
      "fields": {
        "eyebrow": "Senior Digital Strategist",
        "heading": "Ecommerce strategy that works in the real world.",
        "body": "I connect business goals, marketing, operations, and engineering—turning complex commerce challenges into clear, executable plans."
      },
      "preserve": ["primary_cta", "secondary_cta", "media_id"]
    }
  ],
  "sources": [
    {
      "source_id": 3,
      "supports": ["heading", "body"]
    }
  ],
  "warnings": [],
  "unverified_claims": []
}
```

Allowed operations:

- `replace_field`
- `replace_block_fields`
- `insert_block`
- `move_block`
- `remove_block`
- `set_taxonomy`
- `set_related_content`
- `change_slug`
- `create_redirect`

Validate operation payloads through application-owned schemas. Reject unknown operations, fields, block types, styles, HTML, URLs, and embed providers.

Use optimistic concurrency through `expected_revision`. If the underlying content changed after the proposal was generated, mark the proposal stale and require regeneration or a reviewed merge.

## 12. Diff and preview requirements

### Diff

- Word-level diff for plain text and metadata
- Field-level diff for structured values
- Block-level diff for insertions, removals, and movement
- Media thumbnail comparison
- Clear labels for unchanged preserved fields
- Warning banner for slug, canonical, navigation, or indexability changes

### Preview

- Render with the actual public Blade/Livewire components
- Use signed, expiring preview URLs
- Preview must be inaccessible to search engines and excluded from analytics
- Display a persistent preview banner with proposal status and expiration
- Support desktop, tablet, and mobile viewport shortcuts
- Never expose another user's unauthorized draft through a preview URL

## 13. Laravel architecture

### Suggested models

- `AiConversation`
- `AiMessage`
- `ContentProposal`
- `ContentProposalOperation`
- `ContentApproval`
- `ApprovedSource`
- `AssistantProfile`
- Existing `PageRevision`, `PostRevision`, and project revision models

### Suggested services

- `ContentContextBuilder`
- `ApprovedSourceRetriever`
- `ContentAssistantGateway`
- `ProposalSchemaValidator`
- `ProposalPolicyValidator`
- `ContentClaimValidator`
- `ProposalDiffBuilder`
- `ApplyProposalToDraft`
- `ApproveContentProposal`
- `PublishApprovedRevision`
- `RollbackContentRevision`

Controllers and Filament pages should orchestrate these services rather than contain mutation logic.

### AI provider abstraction

Define an application-owned interface so the provider can be replaced:

```php
interface ContentAssistantGateway
{
    public function propose(ContentAssistantRequest $request): ContentProposalData;
}
```

Requirements:

- Provider/model configured through environment and admin-readable settings
- API keys stored only in environment/secret management
- Structured output/schema enforcement
- Request timeout and limited retry policy
- Token/cost usage recorded without logging unnecessary content
- Provider failure does not mutate CMS content
- Development fake for deterministic tests

### Queueing

Queue generation, large source processing, and notification tasks. Show live/polled status in Filament. Requests must be idempotent and protected from double submission.

## 14. Safe command interface for Cursor

Provide application-owned Artisan commands so a developer using Cursor can inspect and create the same proposals without bypassing CMS rules:

```bash
php artisan content:list --type=page
php artisan content:show page home --format=json
php artisan content:propose page home --input=storage/app/content-requests/request.json
php artisan content:validate-proposal {proposal}
php artisan content:preview {proposal}
php artisan content:apply-draft {proposal}
```

Do not provide an Artisan `publish` command in version one. Publishing remains an authenticated Filament action.

Command requirements:

- Use the same services and validation as the UI
- Require an explicit application actor/service account with limited policies
- Default to read-only or draft creation
- Emit structured machine-readable output
- Never accept raw SQL, PHP, shell commands, arbitrary class names, or unrestricted file paths
- Log actor, command, target, request hash, and result

## 15. Security and privacy

- Enforce policies for content reads, source reads, proposal creation, approval, publication, and rollback
- Use CSRF protection and secure authenticated Filament sessions
- Encrypt particularly sensitive source content at rest where appropriate
- Send the model only the minimum relevant context
- Never send contact submissions, user credentials, private analytics data, or unrelated records to the model
- Redact secrets, personal contact details, and internal-only notes before generation
- Treat uploaded/source text as untrusted data, not instructions
- Sanitize all output and prohibit stored scripts/styles
- Rate-limit assistant requests by user and organization
- Add configurable daily cost limits and per-request token limits
- Maintain an append-only audit trail for proposals, approvals, publishing, and rollbacks
- Do not log provider keys, raw authorization headers, or full private source documents

## 16. Observability and analytics

Operational metrics:

- Requests started/completed/failed
- Generation latency
- Provider/model usage and estimated cost
- Validation failure types
- Proposals saved, rejected, approved, and published
- Time from request to publication
- Rollbacks within 24 hours and 7 days

Product events:

- `assistant_conversation_started`
- `assistant_proposal_generated`
- `assistant_proposal_revised`
- `assistant_preview_opened`
- `assistant_draft_saved`
- `assistant_submitted_for_approval`
- `assistant_proposal_approved`
- `assistant_proposal_published`
- `assistant_proposal_rejected`
- `assistant_revision_rolled_back`

Do not put prompts, page copy, user messages, names, emails, or private source content into analytics event properties.

## 17. Accessibility

- Meet WCAG 2.2 AA in the assistant interface
- Conversation, change panel, modal, and diff usable by keyboard
- Announce generation status and validation errors through appropriate live regions
- Do not rely on red/green alone in diffs
- Provide text labels for inserted, removed, and unchanged content
- Move focus predictably after generation and modal actions
- Respect reduced motion
- Ensure preview viewport controls have accessible names and states

## 18. Failure and edge cases

- Provider timeout: retain user message, show retry, create no proposal
- Invalid structured output: retry once with schema feedback, then fail safely
- Stale revision: prevent apply/publish and offer regenerate or reviewed merge
- Deleted target: mark proposal invalid and preserve audit record
- Permission changed mid-conversation: reauthorize every action and block unauthorized access
- Duplicate request: idempotency key prevents duplicate proposals
- Publish failure: prior public revision remains active
- Unsupported request: explain boundary and suggest normal editor or Cursor/developer workflow
- Missing factual support: flag claim and request an approved source
- Prompt injection in source content: ignore source instructions and treat content only as evidence
- Partial block validation failure: reject the entire proposed operation set; do not partially apply silently

## 19. Testing requirements

Use deterministic provider fakes for most automated tests.

Required coverage:

- Role and policy tests for every action
- Proposal JSON schema validation
- Rejection of unknown operations and unsafe HTML/scripts
- Approved-source scoping and redaction
- Unsupported/fabricated claim handling
- Draft-only AI behavior
- Explicit approval and separate publish confirmation
- Revision hash and stale-content protection
- Preview authorization and expiration
- Slug/redirect behavior
- Rollback behavior
- Idempotency and duplicate job protection
- Provider timeout, malformed output, and rate-limit behavior
- Audit-log completeness
- PII exclusion from analytics and application logs
- Filament feature tests for primary flows
- Browser tests for request → proposal → diff → preview → approval → publish
- Accessibility tests for the assistant, diff, and confirmation modal

Do not run live paid model calls in the normal CI test suite.

## 20. Acceptance criteria

Version one is complete when:

1. An editor can select the homepage, request a copy change, and receive a structured proposal without altering published content.
2. The proposal displays a readable diff and validation result.
3. The editor can request a conversational revision that changes only the proposal.
4. The editor can save the proposal as a CMS draft and open a signed preview rendered by the real site templates.
5. An authorized user can approve and separately publish the exact reviewed revision.
6. A changed proposal or stale underlying record invalidates the earlier approval.
7. An author can create a blog draft but cannot publish it.
8. The assistant blocks unsupported code changes and unverified factual claims.
9. Every generation, mutation, approval, publication, rejection, and rollback is attributable in the audit trail.
10. The prior revision can be restored without deleting history.
11. No model call receives secrets, contact submissions, or unrelated private content.
12. Automated tests cover the critical workflow and pass in CI.

## 21. Delivery phases

### Phase 1 — Safe proposal engine

- Provider abstraction and fake
- Conversation/proposal models
- Context builder
- Strict schemas and operation validators
- Diff engine
- Audit events

### Phase 2 — Filament experience

- Conversation interface
- Content selection
- Proposal and diff panel
- Draft application
- Signed preview

### Phase 3 — Approval and publishing

- Approval policies and workflow
- Single-use publish confirmation
- Scheduling
- Stale revision detection
- Rollback

### Phase 4 — Content expansion and Cursor commands

- Blog and case-study creation
- SEO/navigation operations
- Approved source registry
- Safe Artisan inspection/proposal commands
- Cost controls and operational dashboard

## 22. Cursor implementation instructions

1. Treat this PRD and the main portfolio requirements as authoritative.
2. Start by inspecting the actual Page, Post, Project, revision, builder-block, policy, and publishing implementations. Adapt names to the repository rather than duplicating equivalent models.
3. Produce a short implementation plan, schema changes, state-transition table, and threat model before coding.
4. Build the proposal engine independently of Filament so UI and Artisan commands use the same application services.
5. Use strict typed DTOs and JSON schemas at the model boundary. Never pass unvalidated model output to Eloquent.
6. Implement read and proposal creation before draft application; implement publication last.
7. All AI actions default to draft. Do not create a conversational or CLI shortcut around approval.
8. Add deterministic tests with a fake provider before connecting a live provider.
9. Do not invent content or silently broaden the assistant's permissions.
10. Document provider setup, supported actions, permissions, costs, failure recovery, and editor workflow.
11. Record material technical decisions in `docs/decisions.md`.
12. Before completion, map every acceptance criterion to an automated test or a documented manual verification.

## 23. Decisions required before live-provider integration

1. Which AI provider and model should be used?
2. Should assistant conversations and full prompts be retained, and for how long?
3. Which roles can approve and which can publish?
4. Is two-person approval needed for global content, slugs, navigation, or SEO indexability changes?
5. What monthly AI budget and per-user limits should apply?
6. Which resume, bio, project notes, and career documents are approved grounding sources?
7. Should generated content require an “AI-assisted” internal label?
8. Does Lindsey want the safe Cursor Artisan interface in the initial release or after the Filament workflow is proven?

