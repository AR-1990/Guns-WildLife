const http = require('http');
const fs = require('fs');
const path = require('path');

const sessionId = 'top-disappears';
const port = 7777;
const outdir = path.join(process.cwd(), '.dbg');
const logFile = path.join(outdir, `trae-debug-log-${sessionId}.ndjson`);
const envFile = path.join(outdir, `${sessionId}.env`);

fs.mkdirSync(outdir, { recursive: true });
fs.writeFileSync(logFile, '');
fs.writeFileSync(envFile, `DEBUG_SERVER_URL=http://127.0.0.1:${port}/event\nDEBUG_SESSION_ID=${sessionId}\n`);

const headers = {
    'Access-Control-Allow-Origin': '*',
    'Access-Control-Allow-Methods': 'POST, OPTIONS',
    'Access-Control-Allow-Headers': 'Content-Type'
};

const server = http.createServer((req, res) => {
    if (req.method === 'OPTIONS' && req.url === '/event') {
        res.writeHead(204, headers);
        res.end();
        return;
    }

    if (req.method === 'POST' && req.url === '/event') {
        let body = '';
        req.on('data', chunk => body += chunk);
        req.on('end', () => {
            try {
                const event = JSON.parse(body || '{}');
                if (!event.ts) {
                    event.ts = Date.now();
                }
                fs.appendFileSync(logFile, JSON.stringify(event) + '\n');
                res.writeHead(200, { ...headers, 'Content-Type': 'application/json' });
                res.end(JSON.stringify({ ok: true }));
            } catch (error) {
                res.writeHead(400, { ...headers, 'Content-Type': 'application/json' });
                res.end(JSON.stringify({ ok: false, error: error.message }));
            }
        });
        return;
    }

    if (req.method === 'GET' && req.url === '/health') {
        res.writeHead(200, { ...headers, 'Content-Type': 'application/json' });
        res.end(JSON.stringify({ ok: true, sessionId, logFile }));
        return;
    }

    res.writeHead(404, headers);
    res.end();
});

server.listen(port, '127.0.0.1', () => {
    console.log('@@DEBUG_SERVER_INFO');
    console.log(JSON.stringify({
        api_url: `http://127.0.0.1:${port}/event`,
        session_id: sessionId,
        log_dir: outdir,
        log_file: logFile,
        env_file: envFile
    }, null, 2));
    console.log('@@END_DEBUG_SERVER_INFO');
});
