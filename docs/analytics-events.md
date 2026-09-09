# Analytics events

Events are dispatched through `AnalyticsService` and the `window.lwTrack()` helper. **Never include PII** (name, email, message, phone).

## Environment behavior

| Environment | Tracking |
|-------------|----------|
| `local`, `testing` | Disabled unless `ANALYTICS_DEBUG=true` (console logging only) |
| `staging` | Enabled with debug logging |
| `production` | Enabled when Plausible domain or GTM ID is configured |

## Events

### `cta_click`

User clicks a tracked call-to-action.

| Property | Type | Description |
|----------|------|-------------|
| `placement` | string | Location, e.g. `hero`, `case_study`, `footer` |
| `label` | string | Visible CTA text |

### `case_study_view`

Fired on project detail page load (optional client hook).

| Property | Type | Description |
|----------|------|-------------|
| `slug` | string | Project slug |

### `resume_download`

Fired when `/resume` download succeeds.

| Property | Type | Description |
|----------|------|-------------|
| `placement` | string | Link placement |

### `contact_form_start`

User begins completing the contact form.

| Property | Type | Description |
|----------|------|-------------|
| `placement` | string | Always `contact_page` in v1 |

### `contact_form_submit`

Successful contact form submission (server accepted).

| Property | Type | Description |
|----------|------|-------------|
| `placement` | string | Always `contact_page` in v1 |

### `outbound_link_click`

External profile or social link click.

| Property | Type | Description |
|----------|------|-------------|
| `category` | string | e.g. `linkedin`, `email` |

### `blog_post_view`

Insight detail page view.

| Property | Type | Description |
|----------|------|-------------|
| `slug` | string | Post slug |

### `blog_filter_use`

Category filter applied on insights index.

| Property | Type | Description |
|----------|------|-------------|
| `category` | string | Category slug |

### `site_search`

Search performed on `/search`.

| Property | Type | Description |
|----------|------|-------------|
| `query_length` | integer | Character count only—never the raw query if it may contain PII |

### `not_found_view`

404 page rendered.

| Property | Type | Description |
|----------|------|-------------|
| `path` | string | Request path without query string |

## Debug mode

Set `ANALYTICS_DEBUG=true` to log events to the browser console via `window.lwTrack`.
