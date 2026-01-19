# Minitrack

Lightweight Drupal module for pageview and session tracking with a tiny embeddable JS snippet.

See module files for configuration and usage.

## Embed example

```html
<script>
	window.minitrack_key = 'YOUR_API_KEY_HERE';
	window.minitrack_endpoint = 'https://example.com/minitrack/events';
</script>
<script async src="https://example.com/modules/minitrack/js/tracker.js"></script>
```

- Notes:
- Replace `YOUR_API_KEY_HERE` with one of the API keys configured in the module settings. The admin settings provide an "Add API key" button where you can enter a `key` and optional `description` for each entry.
- `window.minitrack_endpoint` defaults to `/minitrack/events` when omitted.

