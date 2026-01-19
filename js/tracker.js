/* Minitrack tiny tracker - minimal functionality
   - Generates visitor/session ids
   - Sends a single pageview event to /minitrack/events
   - Uses sendBeacon or fetch keepalive
*/
(function(w){
  if (!w.minitrack) w.minitrack = {};
  var mt = w.minitrack;

  function uid() { return 'v'+Math.random().toString(36).slice(2,10); }

  mt.getVisitor = function(){
    var v = localStorage.getItem('minitrack_vid');
    if (!v) { v = uid(); localStorage.setItem('minitrack_vid', v); }
    return v;
  };

  mt.getSession = function(){
    var s = sessionStorage.getItem('minitrack_sid');
    if (!s) { s = uid(); sessionStorage.setItem('minitrack_sid', s); }
    return s;
  };

  mt.sendEvent = function(event){
    var payload = { events: [ event ] };
    var url = (window.minitrack_endpoint || '/minitrack/events');
    var body = JSON.stringify(payload);
    var headers = { 'Content-Type': 'application/json' };
    var key = window.minitrack_key || null;
    if (key) headers['X-Minitrack-Key'] = key;

    if (navigator.sendBeacon) {
      try { navigator.sendBeacon(url, body); return; } catch(e){}
    }

    fetch(url, { method: 'POST', body: body, headers: headers, keepalive: true }).catch(function(){});
  };

  // Auto send a pageview
  try {
    mt.sendEvent({ type: 'pageview', path: location.pathname + location.search, host: location.hostname, ts: Math.floor(Date.now()/1000), session: mt.getSession(), visitor: mt.getVisitor(), title: document.title });
  } catch(e){}

})(window);
