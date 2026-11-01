import http from 'node:http';

const appUpstream = new URL(process.env.FMS_PROXY_APP_UPSTREAM ?? 'http://127.0.0.1:8011');
const reverbHost = process.env.FMS_PROXY_REVERB_HOST ?? '127.0.0.1';
const reverbPort = Number(process.env.FMS_PROXY_REVERB_PORT ?? 8080);
const listenHost = process.env.FMS_PROXY_HOST ?? '127.0.0.1';
const listenPort = Number(process.env.FMS_PROXY_PORT ?? 8000);

function isReverbPath(path) {
    return path.startsWith('/app/') || path.startsWith('/apps/');
}

function proxyTarget(requestUrl) {
    if (isReverbPath(requestUrl)) {
        return {
            host: reverbHost,
            port: reverbPort,
        };
    }

    return {
        host: appUpstream.hostname,
        port: Number(appUpstream.port || (appUpstream.protocol === 'https:' ? 443 : 80)),
    };
}

function proxyHeaders(headers, targetHost, forwardedHost) {
    return {
        ...headers,
        host: targetHost,
        'x-forwarded-host': forwardedHost,
        'x-forwarded-proto': headers['x-forwarded-proto'] ?? 'https',
    };
}

const server = http.createServer((request, response) => {
    const target = proxyTarget(request.url ?? '/');
    const upstream = http.request({
        host: target.host,
        port: target.port,
        method: request.method,
        path: request.url,
        headers: proxyHeaders(request.headers, `${target.host}:${target.port}`, request.headers.host ?? ''),
    }, (upstreamResponse) => {
        response.writeHead(upstreamResponse.statusCode ?? 502, upstreamResponse.headers);
        upstreamResponse.pipe(response);
    });

    upstream.on('error', (error) => {
        console.error(`Proxy request failed: ${error.message}`);
        if (!response.headersSent) {
            response.writeHead(502, { 'content-type': 'text/plain; charset=utf-8' });
        }
        response.end('Upstream service unavailable');
    });

    request.pipe(upstream);
});

server.on('upgrade', (request, socket, head) => {
    const target = proxyTarget(request.url ?? '/');
    const upstream = http.request({
        host: target.host,
        port: target.port,
        method: 'GET',
        path: request.url,
        headers: proxyHeaders(request.headers, `${target.host}:${target.port}`, request.headers.host ?? ''),
    });

    upstream.on('upgrade', (response, upstreamSocket, upstreamHead) => {
        const statusText = http.STATUS_CODES[response.statusCode ?? 101] ?? 'Switching Protocols';
        socket.write(`HTTP/1.1 ${response.statusCode ?? 101} ${statusText}\r\n`);
        for (const [name, value] of Object.entries(response.headers)) {
            if (Array.isArray(value)) {
                for (const item of value) socket.write(`${name}: ${item}\r\n`);
            } else if (value !== undefined) {
                socket.write(`${name}: ${value}\r\n`);
            }
        }
        socket.write('\r\n');
        if (upstreamHead.length) upstreamSocket.write(upstreamHead);
        if (head.length) socket.write(head);
        upstreamSocket.pipe(socket);
        socket.pipe(upstreamSocket);
        upstreamSocket.on('error', () => socket.destroy());
        socket.on('error', () => upstreamSocket.destroy());
    });

    upstream.on('response', (response) => {
        const statusText = http.STATUS_CODES[response.statusCode ?? 502] ?? 'Bad Gateway';
        socket.write(`HTTP/1.1 ${response.statusCode ?? 502} ${statusText}\r\n`);
        for (const [name, value] of Object.entries(response.headers)) {
            if (Array.isArray(value)) {
                for (const item of value) socket.write(`${name}: ${item}\r\n`);
            } else if (value !== undefined) {
                socket.write(`${name}: ${value}\r\n`);
            }
        }
        socket.write('\r\n');
        response.pipe(socket);
    });

    upstream.on('error', (error) => {
        console.error(`WebSocket proxy failed: ${error.message}`);
        socket.destroy();
    });

    upstream.end();
});

server.listen(listenPort, listenHost, () => {
    console.log(`Ngrok app/Reverb proxy listening on http://${listenHost}:${listenPort}`);
    console.log(`Laravel upstream: ${appUpstream.host}`);
    console.log(`Reverb upstream: ${reverbHost}:${reverbPort}`);
});
