# Minitrack

Lightweight Drupal 10 module for pageview and session tracking with a tiny embeddable JS snippet.

See module files for configuration and usage.

## Embed example

```html
<script>
	window.minitrack_key = 'YOUR_API_KEY_HERE';
	window.minitrack_endpoint = 'https://example.com/minitrack/events';
</script>
<script async src="https://example.com/modules/minitrack/js/tracker.js"></script>
```

Notes:
- Replace `YOUR_API_KEY_HERE` with the API key configured in the module settings, or leave blank for open ingest (not recommended).
- `window.minitrack_endpoint` defaults to `/minitrack/events` when omitted.

