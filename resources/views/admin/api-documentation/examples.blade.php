@extends('layouts.admin')

@section('title', 'API Integration Examples')

@section('page-title', 'API Integration Examples')

@section('toolbar-buttons')
    <a href="{{ route('admin.api-documentation.index') }}" class="btn btn-primary">
        <i class="bi bi-arrow-left"></i> Back to Documentation
    </a>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="m-0 font-weight-bold text-primary">
                    <i class="bi bi-code-slash me-2"></i>React Native Integration Examples
                </h5>
                <p class="mb-0 mt-2 text-muted">Complete implementation guide for mobile app development</p>
            </div>
            <div class="card-body">
                <!-- Setup Section -->
                <section class="mb-5">
                    <div class="d-flex align-items-center mb-3">
                        <span class="badge bg-primary me-3">1</span>
                        <h6 class="mb-0">Initial Setup</h6>
                    </div>
                    
                    <div class="mb-4">
                        <h6><i class="bi bi-download me-2"></i>Install Dependencies</h6>
                        <pre class="bg-light p-3 rounded"><code># Core API and Storage
npm install axios
npm install @react-native-async-storage/async-storage

# Real-time Communication
npm install pusher-js

# Media Handling
npm install react-native-image-picker
npm install react-native-document-picker

# Navigation (Optional)
npm install @react-navigation/native
npm install @react-navigation/stack</code></pre>
                    </div>

                    <div class="mb-4">
                        <h6><i class="bi bi-gear me-2"></i>API Client Setup</h6>
                        <div class="border rounded">
                            <div class="bg-secondary text-white p-2 d-flex justify-content-between align-items-center">
                                <span class="small font-weight-bold">services/api.js</span>
                                <button class="btn btn-sm btn-outline-light" onclick="copyCode('api-client-code')">
                                    <i class="bi bi-clipboard"></i> Copy
                                </button>
                            </div>
                            <pre id="api-client-code" class="bg-light p-3 mb-0 rounded-bottom"><code>// services/api.js
import axios from 'axios';
import AsyncStorage from '@react-native-async-storage/async-storage';

const API_BASE_URL = '{{ url('/api') }}';

class ApiClient {
  constructor() {
    this.client = axios.create({
      baseURL: API_BASE_URL,
      headers: {
        'Content-Type': 'application/json',
      },
      timeout: 10000, // 10 second timeout
    });

    // Add request interceptor to include auth token
    this.client.interceptors.request.use(
      async (config) => {
        const token = await AsyncStorage.getItem('auth_token');
        if (token) {
          config.headers.Authorization = `Bearer ${token}`;
        }
        return config;
      },
      (error) => {
        return Promise.reject(error);
      }
    );

    // Add response interceptor for error handling
    this.client.interceptors.response.use(
      (response) => response,
      async (error) => {
        if (error.response?.status === 401) {
          // Token expired, redirect to login
          await AsyncStorage.removeItem('auth_token');
          // Navigate to login screen
        }
        return Promise.reject(error);
      }
    );
  }

  // Authentication methods
  async register(userData) {
    const response = await this.client.post('/auth/register', userData);
    if (response.data.success && response.data.data.token) {
      await AsyncStorage.setItem('auth_token', response.data.data.token);
      await AsyncStorage.setItem('user_data', JSON.stringify(response.data.data.user));
    }
    return response.data;
  }

  async login(email, password) {
    const response = await this.client.post('/auth/login', { email, password });
    if (response.data.success && response.data.data.token) {
      await AsyncStorage.setItem('auth_token', response.data.data.token);
      await AsyncStorage.setItem('user_data', JSON.stringify(response.data.data.user));
    }
    return response.data;
  }

  async logout() {
    try {
      await this.client.post('/auth/logout');
    } finally {
      await AsyncStorage.multiRemove(['auth_token', 'user_data']);
    }
  }

  // Chat methods
  async getChats(params = {}) {
    const response = await this.client.get('/chats', { params });
    return response.data;
  }

  async sendMessage(chatId, messageData) {
    const response = await this.client.post(`/chats/${chatId}/messages`, messageData);
    return response.data;
  }

  // Media upload with progress tracking
  async uploadMedia(file, type, onProgress) {
    const formData = new FormData();
    formData.append('file', {
      uri: file.uri,
      type: file.type,
      name: file.fileName || 'upload',
    });
    formData.append('type', type);

    const response = await this.client.post('/media/upload', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
      onUploadProgress: (progressEvent) => {
        if (onProgress) {
          const percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total);
          onProgress(percentCompleted);
        }
      },
    });
    return response.data;
  }
}

export default new ApiClient();</code></pre>
                        </div>
                    </div>
                </section>

                <!-- Real-time Setup -->
                <section class="mb-5">
                    <div class="d-flex align-items-center mb-3">
                        <span class="badge bg-primary me-3">2</span>
                        <h6 class="mb-0">Real-time Setup with Pusher</h6>
                    </div>
                    
                    <div class="border rounded">
                        <div class="bg-secondary text-white p-2 d-flex justify-content-between align-items-center">
                            <span class="small font-weight-bold">services/pusher.js</span>
                            <button class="btn btn-sm btn-outline-light" onclick="copyCode('pusher-code')">
                                <i class="bi bi-clipboard"></i> Copy
                            </button>
                        </div>
                        <pre id="pusher-code" class="bg-light p-3 mb-0 rounded-bottom"><code>// services/pusher.js
// Use standard pusher-js for Expo compatibility
const Pusher = require('pusher-js');

class PusherService {
  constructor() {
    this.pusher = new Pusher('your_pusher_app_key', {
      cluster: 'mt1',
      forceTLS: true,
      authEndpoint: '{{ url('/broadcasting/auth') }}',
      auth: {
        headers: {
          Authorization: `Bearer ${token}`, // Add your token here
        },
      },
    });
    this.userChannel = null;
    this.eventHandlers = new Map();
  }

  connect(userId) {
    // Subscribe to user's private channel
    this.userChannel = this.pusher.subscribe(`private-user.${userId}`);
    
    // Listen for new messages
    this.userChannel.bind('message.sent', (data) => {
      this.handleNewMessage(data);
    });

    // Listen for call events
    this.userChannel.bind('call.started', (data) => {
      this.handleIncomingCall(data);
    });
  }

  // Register event handlers
  onNewMessage(handler) {
    this.eventHandlers.set('newMessage', handler);
  }

  onIncomingCall(handler) {
    this.eventHandlers.set('incomingCall', handler);
  }

  // Event handlers
  handleNewMessage(data) {
    const handler = this.eventHandlers.get('newMessage');
    if (handler) handler(data);
  }

  handleIncomingCall(data) {
    const handler = this.eventHandlers.get('incomingCall');
    if (handler) handler(data);
  }

  disconnect() {
    if (this.userChannel) {
      this.pusher.unsubscribe(this.userChannel.name);
    }
    this.pusher.disconnect();
    this.eventHandlers.clear();
  }
}

export default new PusherService();</code></pre>
                    </div>
                </section>

                <!-- Component Examples -->
                <section class="mb-5">
                    <div class="d-flex align-items-center mb-3">
                        <span class="badge bg-primary me-3">3</span>
                        <h6 class="mb-0">React Component Examples</h6>
                    </div>
                    
                    <div class="row">
                        <div class="col-lg-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="m-0"><i class="bi bi-box-arrow-in-right me-2"></i>Login Component</h6>
                                </div>
                                <div class="card-body p-0">
                                    <div class="border-bottom bg-secondary text-white p-2 d-flex justify-content-between align-items-center">
                                        <span class="small font-weight-bold">components/LoginScreen.js</span>
                                        <button class="btn btn-sm btn-outline-light" onclick="copyCode('login-code')">
                                            <i class="bi bi-clipboard"></i> Copy
                                        </button>
                                    </div>
                                    <pre id="login-code" class="bg-light p-3 mb-0" style="max-height: 400px; overflow-y: auto;"><code>// components/LoginScreen.js
import React, { useState } from 'react';
import { 
  View, 
  Text, 
  TextInput, 
  TouchableOpacity, 
  Alert, 
  ActivityIndicator 
} from 'react-native';
import ApiClient from '../services/api';

const LoginScreen = ({ navigation }) => {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [loading, setLoading] = useState(false);

  const handleLogin = async () => {
    setLoading(true);
    try {
      const response = await ApiClient.login(email, password);
      if (response.success) {
        navigation.replace('MainApp');
      } else {
        Alert.alert('Error', response.message || 'Login failed');
      }
    } catch (error) {
      Alert.alert('Error', error.response?.data?.message || 'Network error');
    } finally {
      setLoading(false);
    }
  };

  return (
    &lt;View style={styles.container}&gt;
      &lt;Text style={styles.title}&gt;ChatApp&lt;/Text&gt;
      
      &lt;TextInput
        style={styles.input}
        placeholder="Email"
        value={email}
        onChangeText={setEmail}
        keyboardType="email-address"
        autoCapitalize="none"
        editable={!loading}
      /&gt;
      
      &lt;TextInput
        style={styles.input}
        placeholder="Password"
        value={password}
        onChangeText={setPassword}
        secureTextEntry
        editable={!loading}
      /&gt;
      
      &lt;TouchableOpacity
        style={styles.button}
        onPress={handleLogin}
        disabled={loading}
      &gt;
        {loading ? (
          &lt;ActivityIndicator color="white" /&gt;
        ) : (
          &lt;Text style={styles.buttonText}&gt;Login&lt;/Text&gt;
        )}
      &lt;/TouchableOpacity&gt;
    &lt;/View&gt;
  );
};

export default LoginScreen;</code></pre>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="m-0"><i class="bi bi-chat-dots me-2"></i>Chat List Component</h6>
                                </div>
                                <div class="card-body p-0">
                                    <div class="border-bottom bg-secondary text-white p-2 d-flex justify-content-between align-items-center">
                                        <span class="small font-weight-bold">components/ChatListScreen.js</span>
                                        <button class="btn btn-sm btn-outline-light" onclick="copyCode('chatlist-code')">
                                            <i class="bi bi-clipboard"></i> Copy
                                        </button>
                                    </div>
                                    <pre id="chatlist-code" class="bg-light p-3 mb-0" style="max-height: 400px; overflow-y: auto;"><code>// components/ChatListScreen.js
import React, { useState, useEffect } from 'react';
import { 
  View, 
  FlatList, 
  Text, 
  TouchableOpacity, 
  RefreshControl,
  Image,
  ActivityIndicator
} from 'react-native';
import ApiClient from '../services/api';
import PusherService from '../services/pusher';

const ChatListScreen = ({ navigation }) => {
  const [chats, setChats] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  useEffect(() => {
    loadChats();
    setupRealtimeUpdates();
    
    return () => {
      PusherService.disconnect();
    };
  }, []);

  const setupRealtimeUpdates = () => {
    PusherService.onNewMessage((data) => {
      updateChatWithNewMessage(data);
    });
  };

  const loadChats = async () => {
    try {
      const response = await ApiClient.getChats();
      if (response.success) {
        setChats(response.data);
      }
    } catch (error) {
      console.error('Error loading chats:', error);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  const renderChatItem = ({ item }) => (
    &lt;TouchableOpacity
      style={styles.chatItem}
      onPress={() =&gt; navigation.navigate('Chat', { 
        chatId: item.id, 
        chatName: item.name 
      })}
    &gt;
      &lt;Image source={{ uri: item.avatar_url }} style={styles.avatar} /&gt;
      &lt;View style={styles.chatContent}&gt;
        &lt;Text style={styles.chatName}&gt;{item.name}&lt;/Text&gt;
        &lt;Text style={styles.lastMessage}&gt;
          {item.last_message?.content || 'No messages yet'}
        &lt;/Text&gt;
      &lt;/View&gt;
    &lt;/TouchableOpacity&gt;
  );

  if (loading) {
    return (
      &lt;View style={styles.loadingContainer}&gt;
        &lt;ActivityIndicator size="large" color="#007bff" /&gt;
      &lt;/View&gt;
    );
  }

  return (
    &lt;FlatList
      data={chats}
      renderItem={renderChatItem}
      keyExtractor={(item) =&gt; item.id.toString()}
      refreshControl={
        &lt;RefreshControl 
          refreshing={refreshing} 
          onRefresh={loadChats}
        /&gt;
      }
    /&gt;
  );
};

export default ChatListScreen;</code></pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Error Handling -->
                <section class="mb-5">
                    <div class="d-flex align-items-center mb-3">
                        <span class="badge bg-primary me-3">4</span>
                        <h6 class="mb-0">Error Handling & Best Practices</h6>
                    </div>
                    
                    <div class="row">
                        <div class="col-lg-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="m-0"><i class="bi bi-shield-check me-2"></i>Error Handler Utility</h6>
                                </div>
                                <div class="card-body p-0">
                                    <pre class="bg-light p-3 mb-0"><code>// utils/errorHandler.js
export const handleApiError = (error) => {
  if (error.response) {
    const { status, data } = error.response;
    
    switch (status) {
      case 401:
        return 'Please log in again';
      case 403:
        return 'You don\'t have permission';
      case 404:
        return 'Resource not found';
      case 422:
        return data.message || 'Validation error';
      case 500:
        return 'Server error. Please try again';
      default:
        return data.message || 'An error occurred';
    }
  } else if (error.request) {
    return 'Network error. Check your connection';
  } else {
    return 'An unexpected error occurred';
  }
};</code></pre>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="m-0"><i class="bi bi-arrow-repeat me-2"></i>Retry Logic</h6>
                                </div>
                                <div class="card-body p-0">
                                    <pre class="bg-light p-3 mb-0"><code>// utils/retryLogic.js
export const retryApiCall = async (apiCall, maxRetries = 3, delay = 1000) => {
  for (let i = 0; i < maxRetries; i++) {
    try {
      return await apiCall();
    } catch (error) {
      if (i === maxRetries - 1) throw error;
      
      // Don't retry on 4xx errors (except 429)
      if (error.response?.status >= 400 && 
          error.response?.status < 500 && 
          error.response?.status !== 429) {
        throw error;
      }
      
      await new Promise(resolve => 
        setTimeout(resolve, delay * Math.pow(2, i))
      );
    }
  }
};</code></pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function copyCode(elementId) {
    const codeElement = document.getElementById(elementId);
    const text = codeElement.textContent;
    
    navigator.clipboard.writeText(text).then(() => {
        // Show success feedback
        const button = event.target.closest('button');
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="bi bi-check"></i> Copied!';
        button.classList.remove('btn-outline-light');
        button.classList.add('btn-success');
        
        setTimeout(() => {
            button.innerHTML = originalText;
            button.classList.remove('btn-success');
            button.classList.add('btn-outline-light');
        }, 2000);
    }).catch(err => {
        console.error('Failed to copy: ', err);
    });
}
</script>
@endpush 