const http = require('http');
const WebSocket = require('ws');

const wss = new WebSocket.Server({ port: 8080 });

// Store all connected clients
const clients = [];

wss.on('connection', (ws) => {
    console.log('✅ New client connected');
    clients.push(ws);

    // Send welcome message
    ws.send(JSON.stringify({
        type: 'connected',
        message: 'Connected to queue notifications'
    }));

    // Handle messages from client
    ws.on('message', (data) => {
        console.log('📨 Message from client:', data.toString());
    });

    // Handle client disconnect
    ws.on('close', () => {
        console.log('❌ Client disconnected');
        const index = clients.indexOf(ws);
        if (index > -1) {
            clients.splice(index, 1);
        }
    });

    // Handle errors
    ws.on('error', (error) => {
        console.log('❌ WebSocket error:', error);
    });
});

// HTTP server for receiving notifications
const server = http.createServer((req, res) => {
    if (req.url === '/notify' && req.method === 'POST') {
        let body = '';
        req.on('data', chunk => { body += chunk; });
        req.on('end', () => {
            try {
                const data = JSON.parse(body);
                
                // Send to all connected clients
                clients.forEach(client => {
                    if (client.readyState === WebSocket.OPEN) {
                        client.send(JSON.stringify({
                            type: 'notification',
                            message: data.message
                        }));
                    }
                });

                res.writeHead(200, { 'Content-Type': 'application/json' });
                res.end(JSON.stringify({ success: true, clients: clients.length }));
            } catch (error) {
                res.writeHead(400, { 'Content-Type': 'application/json' });
                res.end(JSON.stringify({ error: 'Invalid JSON' }));
            }
        });
    } else {
        res.writeHead(404);
        res.end('Not found');
    }
});

server.listen(3000, () => {
    console.log('📡 HTTP server running on http://localhost:3000');
});

console.log('🚀 WebSocket server running on ws://localhost:8080');
console.log('✅ POST to http://localhost:3000/notify to send notifications');