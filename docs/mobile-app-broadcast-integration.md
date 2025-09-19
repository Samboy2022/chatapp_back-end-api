# Mobile App Broadcast Integration Guide

## Overview

This guide shows how mobile/frontend applications should integrate with the new broadcast settings system to dynamically configure WebSocket connections based on admin settings.

## API Endpoints

### 1. Get Broadcast Settings
```
GET /api/broadcast-settings
```

**Response:**
```json
{
  "success": true,
  "data": {
    "enabled": true,
    "driver": "pusher",
    "config": {
      "enabled": true,
      "driver": "pusher",
      "key": "b3652bc3e7cddc5d6f80",
      "cluster": "mt1",
      "host": null,
      "port": 443,
      "scheme": "https",
      "encrypted": true,
      "auth_endpoint": "http://127.0.0.1:8000/broadcasting/auth"
    },
    "connection_status": {
      "connected": false,
      "message": "Pusher Cloud connection failed"
    },
    "last_updated": "2025-07-14T19:33:57.000000Z"
  }
}
```

### 2. Get Connection Info (Lightweight)
```
GET /api/broadcast-settings/connection-info
```

### 3. Get Status (For Polling)
```
GET /api/broadcast-settings/status
```

### 4. Health Check
```
GET /api/broadcast-settings/health
```

## React Native Implementation

### 1. Configuration Service

```javascript
// services/BroadcastConfigService.js
import AsyncStorage from '@react-native-async-storage/async-storage';

class BroadcastConfigService {
  constructor() {
    this.config = null;
    this.lastFetch = null;
    this.cacheTimeout = 5 * 60 * 1000; // 5 minutes
  }

  async getConfig(forceRefresh = false) {
    try {
      // Check cache first
      if (!forceRefresh && this.config && this.lastFetch) {
        const now = Date.now();
        if (now - this.lastFetch < this.cacheTimeout) {
          return this.config;
        }
      }

      // Fetch from API
      const response = await fetch(`${API_BASE_URL}/api/broadcast-settings`);
      const data = await response.json();

      if (data.success) {
        this.config = data.data;
        this.lastFetch = Date.now();
        
        // Cache for offline use
        await AsyncStorage.setItem('broadcast_config', JSON.stringify(this.config));
        
        return this.config;
      } else {
        throw new Error(data.message || 'Failed to get broadcast config');
      }
    } catch (error) {
      console.error('Failed to get broadcast config:', error);
      
      // Try to use cached config
      try {
        const cached = await AsyncStorage.getItem('broadcast_config');
        if (cached) {
          this.config = JSON.parse(cached);
          return this.config;
        }
      } catch (cacheError) {
        console.error('Failed to get cached config:', cacheError);
      }
      
      // Return default disabled config
      return {
        enabled: false,
        driver: 'log',
        config: { enabled: false }
      };
    }
  }

  async isEnabled() {
    const config = await this.getConfig();
    return config.enabled && config.config.enabled;
  }

  async getConnectionConfig() {
    const config = await this.getConfig();
    return config.config;
  }
}

export default new BroadcastConfigService();
```

### 2. WebSocket Connection Manager

```javascript
// services/WebSocketManager.js
import { Pusher } from '@pusher/pusher-websocket-react-native';
import BroadcastConfigService from './BroadcastConfigService';

class WebSocketManager {
  constructor() {
    this.pusher = null;
    this.isConnected = false;
    this.reconnectAttempts = 0;
    this.maxReconnectAttempts = 5;
  }

  async initialize() {
    try {
      // Check if broadcasting is enabled
      const isEnabled = await BroadcastConfigService.isEnabled();
      if (!isEnabled) {
        console.log('Broadcasting is disabled, skipping WebSocket initialization');
        return false;
      }

      // Get connection configuration
      const config = await BroadcastConfigService.getConnectionConfig();
      
      if (config.driver === 'pusher') {
        await this.initializePusher(config);
      } else if (config.driver === 'reverb') {
        await this.initializeReverb(config);
      } else {
        console.log('Unknown driver:', config.driver);
        return false;
      }

      return true;
    } catch (error) {
      console.error('Failed to initialize WebSocket:', error);
      return false;
    }
  }

  async initializePusher(config) {
    this.pusher = Pusher.getInstance();
    
    await this.pusher.init({
      apiKey: config.key,
      cluster: config.cluster || undefined,
      host: config.host || undefined,
      port: config.port || 443,
      useTLS: config.encrypted || true,
      authEndpoint: config.auth_endpoint,
      auth: {
        headers: {
          Authorization: `Bearer ${await this.getAuthToken()}`
        }
      }
    });

    await this.pusher.connect();
    this.setupEventListeners();
    
    console.log('Pusher initialized successfully');
  }

  async initializeReverb(config) {
    this.pusher = Pusher.getInstance();
    
    await this.pusher.init({
      apiKey: config.key,
      host: config.host,
      port: config.port,
      useTLS: config.scheme === 'https',
      authEndpoint: config.auth_endpoint,
      auth: {
        headers: {
          Authorization: `Bearer ${await this.getAuthToken()}`
        }
      }
    });

    await this.pusher.connect();
    this.setupEventListeners();
    
    console.log('Reverb initialized successfully');
  }

  setupEventListeners() {
    this.pusher.connection.bind('connected', () => {
      console.log('WebSocket connected');
      this.isConnected = true;
      this.reconnectAttempts = 0;
    });

    this.pusher.connection.bind('disconnected', () => {
      console.log('WebSocket disconnected');
      this.isConnected = false;
      this.handleReconnection();
    });

    this.pusher.connection.bind('error', (error) => {
      console.error('WebSocket error:', error);
      this.handleReconnection();
    });
  }

  async handleReconnection() {
    if (this.reconnectAttempts >= this.maxReconnectAttempts) {
      console.log('Max reconnection attempts reached');
      return;
    }

    this.reconnectAttempts++;
    console.log(`Reconnection attempt ${this.reconnectAttempts}`);

    // Wait before reconnecting
    await new Promise(resolve => setTimeout(resolve, 2000 * this.reconnectAttempts));

    // Check if settings changed
    const config = await BroadcastConfigService.getConfig(true);
    if (!config.enabled) {
      console.log('Broadcasting disabled, stopping reconnection');
      return;
    }

    // Reinitialize connection
    await this.initialize();
  }

  async getAuthToken() {
    // Get your auth token from secure storage
    const token = await AsyncStorage.getItem('auth_token');
    return token;
  }

  subscribe(channelName) {
    if (!this.pusher || !this.isConnected) {
      console.warn('WebSocket not connected, cannot subscribe to channel:', channelName);
      return null;
    }

    return this.pusher.subscribe(channelName);
  }

  disconnect() {
    if (this.pusher) {
      this.pusher.disconnect();
      this.pusher = null;
      this.isConnected = false;
    }
  }
}

export default new WebSocketManager();
```

### 3. App Initialization

```javascript
// App.js or main component
import React, { useEffect, useState } from 'react';
import WebSocketManager from './services/WebSocketManager';
import BroadcastConfigService from './services/BroadcastConfigService';

export default function App() {
  const [broadcastEnabled, setBroadcastEnabled] = useState(false);
  const [wsConnected, setWsConnected] = useState(false);

  useEffect(() => {
    initializeBroadcasting();
    
    // Check for config updates every 5 minutes
    const interval = setInterval(checkConfigUpdates, 5 * 60 * 1000);
    
    return () => clearInterval(interval);
  }, []);

  const initializeBroadcasting = async () => {
    try {
      const config = await BroadcastConfigService.getConfig();
      setBroadcastEnabled(config.enabled);
      
      if (config.enabled) {
        const connected = await WebSocketManager.initialize();
        setWsConnected(connected);
      }
    } catch (error) {
      console.error('Failed to initialize broadcasting:', error);
    }
  };

  const checkConfigUpdates = async () => {
    try {
      const config = await BroadcastConfigService.getConfig(true);
      
      if (config.enabled !== broadcastEnabled) {
        setBroadcastEnabled(config.enabled);
        
        if (config.enabled) {
          // Broadcasting was enabled
          const connected = await WebSocketManager.initialize();
          setWsConnected(connected);
        } else {
          // Broadcasting was disabled
          WebSocketManager.disconnect();
          setWsConnected(false);
        }
      }
    } catch (error) {
      console.error('Failed to check config updates:', error);
    }
  };

  return (
    <View>
      {/* Your app content */}
      <StatusBar 
        backgroundColor={wsConnected ? 'green' : 'orange'} 
        title={`WebSocket: ${wsConnected ? 'Connected' : 'Disconnected'}`}
      />
    </View>
  );
}
```

## Key Features

### ✅ Dynamic Configuration
- Mobile app checks API for current broadcast settings
- Automatically adapts to admin changes
- No app restart required for configuration changes

### ✅ Offline Support
- Caches last known configuration
- Graceful fallback when API is unavailable
- Works with cached settings until reconnection

### ✅ Automatic Reconnection
- Handles connection failures gracefully
- Exponential backoff for reconnection attempts
- Respects admin disable/enable settings

### ✅ Driver Switching
- Supports both Pusher Cloud and Laravel Reverb
- Seamless switching between drivers
- Proper cleanup when switching

### ✅ Status Monitoring
- Real-time connection status
- Health check integration
- Error reporting and logging

## Testing

Use the provided API endpoints to test different scenarios:

1. **Enable/Disable Broadcasting**: Toggle in admin panel
2. **Switch Drivers**: Change between Pusher and Reverb
3. **Update Credentials**: Modify API keys and test reconnection
4. **Connection Failures**: Test offline scenarios

This integration ensures your mobile app always uses the correct broadcast configuration as set by administrators, with proper fallbacks and error handling.
