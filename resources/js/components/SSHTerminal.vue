<template>
  <div class="ssh-terminal-container">
    <div class="terminal-header">
      <h3>SSH Terminal - {{ projectName }}</h3>
      <div class="terminal-controls">
        <button @click="reconnect" class="btn-sm" :disabled="connecting">
          <i class="fas fa-sync-alt"></i> Reconnect
        </button>
        <button @click="clearTerminal" class="btn-sm">
          <i class="fas fa-eraser"></i> Clear
        </button>
      </div>
    </div>
    
    <div class="terminal-status" :class="statusClass">
      <span class="status-indicator"></span>
      {{ connectionStatus }}
    </div>

    <div ref="terminal" class="terminal-window"></div>

    <div class="terminal-footer">
      <span class="text-muted">Press Ctrl+C to interrupt | Ctrl+D to exit</span>
    </div>
  </div>
</template>

<script>
import { Terminal } from 'xterm';
import { FitAddon } from 'xterm-addon-fit';
import 'xterm/css/xterm.css';

export default {
  name: 'SSHTerminal',
  
  props: {
    projectId: {
      type: [Number, String],
      required: true
    },
    projectName: {
      type: String,
      default: 'Project'
    }
  },

  data() {
    return {
      terminal: null,
      fitAddon: null,
      ws: null,
      connecting: false,
      connected: false,
      connectionStatus: 'Disconnected'
    };
  },

  computed: {
    statusClass() {
      if (this.connected) return 'status-connected';
      if (this.connecting) return 'status-connecting';
      return 'status-disconnected';
    }
  },

  mounted() {
    this.initTerminal();
    this.connect();
  },

  beforeUnmount() {
    this.disconnect();
    if (this.terminal) {
      this.terminal.dispose();
    }
  },

  methods: {
    initTerminal() {
      this.terminal = new Terminal({
        cursorBlink: true,
        fontSize: 14,
        fontFamily: 'Menlo, Monaco, "Courier New", monospace',
        theme: {
          background: '#1e1e1e',
          foreground: '#d4d4d4',
          cursor: '#ffffff',
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
        cols: 80,
        rows: 24
      });

      this.fitAddon = new FitAddon();
      this.terminal.loadAddon(this.fitAddon);
      this.terminal.open(this.$refs.terminal);
      this.fitAddon.fit();

      // Handle terminal input
      this.terminal.onData((data) => {
        if (this.ws && this.ws.readyState === WebSocket.OPEN) {
          this.ws.send(JSON.stringify({
            type: 'input',
            data: data
          }));
        }
      });

      // Handle window resize
      window.addEventListener('resize', () => {
        this.fitAddon.fit();
      });
    },

    connect() {
      this.connecting = true;
      this.connectionStatus = 'Connecting...';

      const protocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
      const wsUrl = `${protocol}//${window.location.host}/ws/ssh/${this.projectId}`;

      this.ws = new WebSocket(wsUrl);

      this.ws.onopen = () => {
        this.connected = true;
        this.connecting = false;
        this.connectionStatus = 'Connected';
        this.terminal.write('\r\n\x1b[32mConnected to SSH terminal\x1b[0m\r\n');
      };

      this.ws.onmessage = (event) => {
        try {
          const message = JSON.parse(event.data);
          if (message.type === 'output') {
            this.terminal.write(message.data);
          }
        } catch (e) {
          this.terminal.write(event.data);
        }
      };

      this.ws.onerror = (error) => {
        console.error('WebSocket error:', error);
        this.connectionStatus = 'Connection error';
        this.terminal.write('\r\n\x1b[31mConnection error\x1b[0m\r\n');
      };

      this.ws.onclose = () => {
        this.connected = false;
        this.connecting = false;
        this.connectionStatus = 'Disconnected';
        this.terminal.write('\r\n\x1b[33mConnection closed\x1b[0m\r\n');
      };
    },

    disconnect() {
      if (this.ws) {
        this.ws.close();
        this.ws = null;
      }
    },

    reconnect() {
      this.disconnect();
      setTimeout(() => {
        this.connect();
      }, 500);
    },

    clearTerminal() {
      this.terminal.clear();
    }
  }
};
</script>

<style scoped>
.ssh-terminal-container {
  display: flex;
  flex-direction: column;
  height: 100%;
  background: #1e1e1e;
  border-radius: 8px;
  overflow: hidden;
}

.terminal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  background: #252526;
  border-bottom: 1px solid #3e3e42;
}

.terminal-header h3 {
  margin: 0;
  color: #d4d4d4;
  font-size: 1rem;
}

.terminal-controls {
  display: flex;
  gap: 0.5rem;
}

.terminal-status {
  padding: 0.5rem 1rem;
  font-size: 0.875rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.status-indicator {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  display: inline-block;
}

.status-connected {
  background: #1e3a20;
  color: #4caf50;
}

.status-connected .status-indicator {
  background: #4caf50;
  box-shadow: 0 0 8px #4caf50;
}

.status-connecting {
  background: #3a311e;
  color: #ff9800;
}

.status-connecting .status-indicator {
  background: #ff9800;
  animation: pulse 1.5s ease-in-out infinite;
}

.status-disconnected {
  background: #3a1e1e;
  color: #f44336;
}

.status-disconnected .status-indicator {
  background: #f44336;
}

.terminal-window {
  flex: 1;
  padding: 1rem;
  overflow: hidden;
}

.terminal-footer {
  padding: 0.5rem 1rem;
  background: #252526;
  border-top: 1px solid #3e3e42;
  font-size: 0.75rem;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}
</style>