const http = require('http');
const fs = require('fs');
const path = require('path');

const sessionId = 'admin-sales-select';
const port = 7779;
const outdir = path.join(process.cwd(), '.dbg');
const logFile = path.join(outdir, `trae-debug-log-${sessionId}.ndjson`);
const envFile = path.join(outdir, `${sessionId}.env`);

fs.mkdirSync(outdir, { recursive: true });
fs.writeFileSync(logFile, '');
fs.writeFileSync(envFile, `DEBUG_SERVER_URL=http://127.0.0.1:${port}/event\nDEBUG_SESSION_ID=${sessionId}\n`);

const writeEvent = (payload) => {
  fs.appendFileSync(logFile, JSON.stringify({
    ts: new Date().toISOString(),
    ...payload,
  }) + '\n');
};

const server = http.createServer((req, res) => {
  if (req.method === 'OPTIONS') {
    res.writeHead(204, {
      'Access-Control-Allow-Origin': '*',
      'Access-Control-Allow-Methods': 'POST, OPTIONS, GET',
      'Access-Control-Allow-Headers': 'Content-Type',
    });
    return res.end();
  }

  if (req.method === 'GET' && req.url === '/health') {
    res.writeHead(200, { 'Content-Type': 'application/json', 'Access-Control-Allow-Origin': '*' });
    return res.end(JSON.stringify({ ok: true, sessionId, logFile }));
  }

  if (req.method === 'POST' && req.url === '/event') {
    let body = '';
    req.on('data', (chunk) => {
      body += chunk;
    });
    req.on('end', () => {
      try {
        writeEvent(JSON.parse(body || '{}'));
        res.writeHead(202, { 'Content-Type': 'application/json', 'Access-Control-Allow-Origin': '*' });
        res.end(JSON.stringify({ ok: true }));
      } catch (error) {
        res.writeHead(400, { 'Content-Type': 'application/json', 'Access-Control-Allow-Origin': '*' });
        res.end(JSON.stringify({ ok: false, error: error.message }));
      }
    });
    return;
  }

  res.writeHead(404, { 'Content-Type': 'application/json', 'Access-Control-Allow-Origin': '*' });
  res.end(JSON.stringify({ ok: false }));
});

server.listen(port, '127.0.0.1', () => {
  console.log(JSON.stringify({
    api_url: `http://127.0.0.1:${port}/event`,
    session_id: sessionId,
    log_file: logFile,
  }));
});
