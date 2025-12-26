@extends('layouts.app')

@section('title', 'SSH Terminal')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/xterm@5.3.0/css/xterm.css" />
<style>
    #terminal-container {
        height: calc(100vh - 200px);
        background-color: #1e1e1e;
    }
    .terminal {
        padding: 10px;
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">SSH Terminal</h1>
                    <p class="mt-1 text-sm text-gray-600">Web-based SSH access to your project server</p>
                </div>
                <div class="flex items-center gap-3">
                    <!-- Connection Status -->
                    <div class="flex items-center gap-2 px-3 py-2 bg-gray-100 rounded-lg">
                        <div id="connection-status" class="w-3 h-3 rounded-full bg-gray-400"></div>
                        <span id="connection-text" class="text-sm font-medium text-gray-700">Disconnected</span>
                    </div>
                    
                    <!-- Terminal Controls -->
                    <div class="flex items-center gap-2">
                        <button onclick="clearTerminal()" 
                            class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition duration-150" 
                            title="Clear Terminal">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                        <button onclick="reconnectSSH()" 
                            class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition duration-150" 
                            title="Reconnect">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                        </button>
                        <button onclick="downloadSession()" 
                            class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition duration-150" 
                            title="Download Session Log">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                        </button>
                        <button onclick="showSettings()" 
                            class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition duration-150" 
                            title="Terminal Settings">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- Connection Info Bar -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3 flex-1">
                    <h3 class="text-sm font-medium text-blue-800">Connection Information</h3>
                    <div class="mt-2 text-sm text-blue-700 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <span class="font-medium">Project:</span> 
                            <span id="project-name">{{ $project->name }}</span>
                        </div>
                        <div>
                            <span class="font-medium">Host:</span> 
                            <span id="host-info">{{ $project->server_ip }}</span>
                        </div>
                        <div>
                            <span class="font-medium">User:</span> 
                            <span id="user-info">{{ $project->ssh_user }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Terminal Container -->
        <div class="bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden">
            <div class="bg-gray-800 px-4 py-2 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded-full bg-red-500"></div>
                    <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                    <div class="w-3 h-3 rounded-full bg-green-500"></div>
                    <span class="ml-4 text-sm text-gray-300 font-mono">{{ $project->ssh_user }}@{{ $project->server_ip }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-gray-400 font-mono" id="terminal-time"></span>
                </div>
            </div>
            <div id="terminal-container" class="relative"></div>
        </div>

        <!-- Quick Commands -->
        <div class="mt-6 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Commands</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                <button onclick="sendCommand('ls -la')" 
                    class="flex items-center justify-center gap-2 px-4 py-3 bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded-lg transition duration-150">
                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                    </svg>
                    <span class="text-sm font-medium text-gray-700">List Files</span>
                </button>
                <button onclick="sendCommand('df -h')" 
                    class="flex items-center justify-center gap-2 px-4 py-3 bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded-lg transition duration-150">
                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <span class="text-sm font-medium text-gray-700">Disk Usage</span>
                </button>
                <button onclick="sendCommand('top -n 1 | head -20')" 
                    class="flex items-center justify-center gap-2 px-4 py-3 bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded-lg transition duration-150">
                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <span class="text-sm font-medium text-gray-700">Process Monitor</span>
                </button>
                <button onclick="sendCommand('php artisan --version')" 
                    class="flex items-center justify-center gap-2 px-4 py-3 bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded-lg transition duration-150">
                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                    </svg>
                    <span class="text-sm font-medium text-gray-700">Laravel Version</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Settings Modal -->
<div id="settingsModal" class="hidden fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Terminal Settings</h3>
            <div class="space-y-4">
                <div>
                    <label for="fontSize" class="block text-sm font-medium text-gray-700 mb-1">Font Size</label>
                    <input type="number" id="fontSize" value="14" min="8" max="24"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>
                <div>
                    <label for="fontFamily" class="block text-sm font-medium text-gray-700 mb-1">Font Family</label>
                    <select id="fontFamily"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <option value="'Courier New', monospace">Courier New</option>
                        <option value="'Monaco', monospace">Monaco</option>
                        <option value="'Consolas', monospace" selected>Consolas</option>
                        <option value="'Ubuntu Mono', monospace">Ubuntu Mono</option>
                    </select>
                </div>
                <div>
                    <label for="cursorBlink" class="flex items-center">
                        <input type="checkbox" id="cursorBlink" checked
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-700">Cursor Blink</span>
                    </label>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button onclick="hideSettings()" 
                    class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition duration-150">
                    Cancel
                </button>
                <button onclick="applySettings()" 
                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition duration-150">
                    Apply
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/xterm@5.3.0/lib/xterm.js"></script>
<script src="https://cdn.jsdelivr.net/npm/xterm-addon-fit@0.8.0/lib/xterm-addon-fit.js"></script>
<script src="https://cdn.jsdelivr.net/npm/xterm-addon-web-links@0.9.0/lib/xterm-addon-web-links.js"></script>
<script>
let terminal;
let fitAddon;
let websocket;
let sessionLog = [];

function initTerminal() {
    // Initialize xterm.js
    terminal = new Terminal({
        cursorBlink: true,
        fontSize: 14,
        fontFamily: 'Consolas, monospace',
        theme: {
            background: '#1e1e1e',
            foreground: '#d4d4d4',
            cursor: '#d4d4d4',
            black: '#000000',
            red: '#cd3131',
            green: '#0dbc79',
            yellow: '#e5e510',
            blue: '#2472c8',
            magenta: '#bc3fbc',
            cyan: '#11a8cd',
            white: '#e5e5e5',
            brightBlack: '#666666',
            brightRed: '#f14c4c',
            brightGreen: '#23d18b',
            brightYellow: '#f5f543',
            brightBlue: '#3b8eea',
            brightMagenta: '#d670d6',
            brightCyan: '#29b8db',
            brightWhite: '#e5e5e5'
        },
        scrollback: 10000,
        tabStopWidth: 4
    });

    // Add fit addon
    fitAddon = new FitAddon.FitAddon();
    terminal.loadAddon(fitAddon);

    // Add web links addon
    terminal.loadAddon(new WebLinksAddon.WebLinksAddon());

    // Open terminal
    terminal.open(document.getElementById('terminal-container'));
    fitAddon.fit();

    // Handle resize
    window.addEventListener('resize', () => {
        fitAddon.fit();
    });

    // Connect WebSocket
    connectWebSocket();

    // Update time
    setInterval(updateTime, 1000);
}

function connectWebSocket() {
    const protocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
    const wsUrl = `${protocol}//${window.location.host}/ws/ssh/{{ $project->id }}`;
    
    websocket = new WebSocket(wsUrl);

    websocket.onopen = function() {
        updateConnectionStatus('connected');
        terminal.write('\r\n\x1b[32mConnected to SSH server\x1b[0m\r\n\r\n');
    };

    websocket.onmessage = function(event) {
        terminal.write(event.data);
        sessionLog.push(event.data);
    };

    websocket.onerror = function(error) {
        updateConnectionStatus('error');
        terminal.write('\r\n\x1b[31mConnection error\x1b[0m\r\n');
    };

    websocket.onclose = function() {
        updateConnectionStatus('disconnected');
        terminal.write('\r\n\x1b[33mConnection closed\x1b[0m\r\n');
    };

    // Send input to server
    terminal.onData(data => {
        if (websocket.readyState === WebSocket.OPEN) {
            websocket.send(data);
            sessionLog.push(data);
        }
    });
}

function updateConnectionStatus(status) {
    const statusEl = document.getElementById('connection-status');
    const textEl = document.getElementById('connection-text');
    
    switch(status) {
        case 'connected':
            statusEl.className = 'w-3 h-3 rounded-full bg-green-500 animate-pulse';
            textEl.textContent = 'Connected';
            break;
        case 'disconnected':
            statusEl.className = 'w-3 h-3 rounded-full bg-gray-400';
            textEl.textContent = 'Disconnected';
            break;
        case 'error':
            statusEl.className = 'w-3 h-3 rounded-full bg-red-500';
            textEl.textContent = 'Error';
            break;
    }
}

function clearTerminal() {
    terminal.clear();
}

function reconnectSSH() {
    if (websocket) {
        websocket.close();
    }
    terminal.clear();
    connectWebSocket();
}

function sendCommand(command) {
    if (websocket.readyState === WebSocket.OPEN) {
        websocket.send(command + '\n');
        sessionLog.push(command + '\n');
    }
}

function downloadSession() {
    const blob = new Blob([sessionLog.join('')], { type: 'text/plain' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `ssh-session-${new Date().toISOString()}.txt`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
}

function showSettings() {
    document.getElementById('settingsModal').classList.remove('hidden');
}

function hideSettings() {
    document.getElementById('settingsModal').classList.add('hidden');
}

function applySettings() {
    const fontSize = parseInt(document.getElementById('fontSize').value);
    const fontFamily = document.getElementById('fontFamily').value;
    const cursorBlink = document.getElementById('cursorBlink').checked;

    terminal.options.fontSize = fontSize;
    terminal.options.fontFamily = fontFamily;
    terminal.options.cursorBlink = cursorBlink;
    
    fitAddon.fit();
    hideSettings();
}

function updateTime() {
    const now = new Date();
    document.getElementById('terminal-time').textContent = now.toLocaleTimeString();
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', initTerminal);

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    if (websocket) {
        websocket.close();
    }
});
</script>
@endpush