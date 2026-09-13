const http = require('http');
const fs = require('fs');
const path = require('path');

const sessionId = 'admin-login-hangs';
const port = 7778;
const outdir = path.join(process.cwd(), '.dbg');
const logFile = path.join(outdir, `trae-debug-log-${sessionId}.ndjson`);
const envFile = path.join(outdir, `${sessionId}.env`);

fs.mkdirSync(outdir, { recursive: true });
fs.writeFileSync(logFile, '');
fs.writeFileSync(envFile, `DEBUG_SERVER_URL=http://127.0.0.1:${port}/event\nDEBUG_SESSION_ID=${sessionId}\n`);

const headers = {
    'Access-Control-Allow-Origin': '*',
    'Access-Control-Allow-Methods': 'POST, OPTIONS, GET',
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

    if (req.method === 'GET' && req.url === '/logs') {
        res.writeHead(200, { ...headers, 'Content-Type': 'text/plain; charset=utf-8' });
        res.end(fs.existsSync(logFile) ? fs.readFileSync(logFile, 'utf8') : '');
        return;
    }

    res.writeHead(404, headers);
    res.end();
});

server.listen(port, '127.0.0.1', () => {
    process.stdout.write('@@DEBUG_SERVER_INFO\n');
    process.stdout.write(JSON.stringify({
        api_url: `http://127.0.0.1:${port}/event`,
        session_id: sessionId,
        log_dir: outdir,
        log_file: logFile,
        env_file: envFile
    }, null, 2));
    process.stdout.write('\n@@END_DEBUG_SERVER_INFO\n');
});
