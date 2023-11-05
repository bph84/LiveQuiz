
import { WebSocketServer } from 'ws';
import config from './config.js';

const wss = new WebSocketServer({ port: 7071 });

let connectionCount = 0;
const allClients = new Map();
let masterConnection;

let currentState = {};
let currentCorrectAnswer; 

let notifyMaster = (message) => {};
let notifyViewer = (message) => {};

setInterval(() => {
    allClients.forEach((client) => {
        client.send(JSON.stringify({ping: true}));
    });
}, 20000);


const log = (message) => {
    console.log(message);
}

function tryParseJSONObject (jsonString){
    try {
        var o = JSON.parse(jsonString);

        if (o && typeof o === "object") {
            return o;
        }
    }
    catch (e) { }

    return false;
};

function handleMasterMessage(messageBuffer) {
    log("Master message!" + messageBuffer.toString('utf-8'));
    const message = tryParseJSONObject(messageBuffer);

    if (!message) {
        log("Message is not JSON");
        return;
    }

    if (message.newHtml) {
        log("New HTML from master");
        
        currentState.currentHTML = message.newHtml;

        allClients.forEach((client) => {
            client.send(JSON.stringify({newHtml: message.newHtml}));
        });
    }

    if (message.correctAnswer) {
        currentCorrectAnswer = message.correctAnswer;
    }

    if (message.showCorrectAnswer) {
        allClients.forEach((client) => {

            let clientCorrectness = false;
            if (client.currentAnswer == currentCorrectAnswer) {
                clientCorrectness = true;
            }

            client.send(
                JSON.stringify({
                    correctAnswer: currentCorrectAnswer,
                    markedAsCorrect: clientCorrectness
                })
            );
        });
    }
    
    if (message.directControl) {
        allClients.forEach((client) => {
            client.send(JSON.stringify({directControl: message.directControl}));
        });
    }
}

function handleViewerMessage(messageBuffer) {

    // This doesn't really do anything!?

    log("Viewer message!" + messageBuffer.toString('utf-8'));
}


wss.on('connection', (ws, request) => {

    connectionCount++;
    allClients.set(connectionCount, ws);
    ws.myConnectionId = connectionCount;

    log("New connection from " + ws._socket.remoteAddress + ", connectionId: " + ws.myConnectionId);

    if(request.headers.cookie) {
        const cookies = request.headers.cookie.split(';');
        cookies.forEach((cookie) => {
            const [name, value] = cookie.trim().split('=');
            if (name === 'usersName') {
                console.log(value);
                ws.usersName = value;
                ws.usefulName = ws.usersName + " (" + ws.myConnectionId + ")";
            }
        });
    } else {
        console.log("No cookies!");
    }
  
    setTimeout( () => {
        
        ws.send(JSON.stringify({connectionId: connectionCount}));

        ws.send(JSON.stringify({newHtml: currentState.currentHTML}));

        notifyViewer({
            message: "New websocket connection", 
            connectionId: connectionCount,
            sourceIP: ws._socket.remoteAddress,
            usefulName: ws.usefulName
        });

    }, 100);


    ws.on('message', (messageBuffer) => {
        log("Normal message from " + ws.myConnectionId + ": "  + messageBuffer.toString('utf-8'));
        const message = tryParseJSONObject(messageBuffer);

        if (!message) {
            log("Message is not JSON");
            return;
        }

        if (message.type) {
            if (message.type == "answer") {
                notifyViewer({
                    message: "Answer from " + ws.usefulName + ": " + message.answer
                });

                ws.currentAnswer = message.answer;
            }
        }

        if (message.authorize) {
            if (message.key == config.masterKey) {

                masterConnection = ws;
                ws.send(JSON.stringify({authorized: true, authType: 'master'}));
                notifyMaster = (message) => {
                    ws.send(JSON.stringify(message));
                }

                ws.removeEventListener('message');

                ws.on('message', (messageBuffer) => {
                    handleMasterMessage(messageBuffer);
                });

            } else if (message.key == config.viewerKey) {

                ws.send(JSON.stringify({authorized: true, authType: 'viewer'}));
                notifyViewer = (message) => {
                    ws.send(JSON.stringify(message));
                }

                ws.removeEventListener('message');

                ws.on('message', (messageBuffer) => {
                    handleViewerMessage(messageBuffer);
                });

            } else {
                ws.send(JSON.stringify({authorized: false}));
            }
        }
    });

    ws.on('close', () => {
        log("Connection closed, connectionId: " + ws.myConnectionId);
        allClients.delete(ws.myConnectionId);
    }
    );

    ws.on('error', (error) => {
        log("Error: " + error);
    }
    );

});

