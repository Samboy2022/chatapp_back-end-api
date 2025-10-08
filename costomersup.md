# Product Requirements Document (PRD)
## AI-Powered Customer Support Chat Platform

---

## **Document Information**

| **Field** | **Details** |
|-----------|-------------|
| **Product Name** | ChatSupport AI |
| **Version** | 1.0 |
| **Date** | October 5, 2025 |
| **Author** | Product Team |
| **Status** | Draft |
| **Document Type** | Product Requirements Document |

---

## **Table of Contents**

1. [Executive Summary](#1-executive-summary)
2. [Product Overview](#2-product-overview)
3. [User Personas](#3-user-personas)
4. [User Stories](#4-user-stories)
5. [Functional Requirements](#5-functional-requirements)
6. [Technical Architecture](#6-technical-architecture)
7. [System Design](#7-system-design)
8. [Application Flow](#8-application-flow)
9. [UI/UX Design Specifications](#9-uiux-design-specifications)
10. [API Specifications](#10-api-specifications)
11. [Database Schema](#11-database-schema)
12. [Security Requirements](#12-security-requirements)
13. [Performance Requirements](#13-performance-requirements)
14. [Integration Requirements](#14-integration-requirements)
15. [Analytics & Metrics](#15-analytics--metrics)
16. [Deployment Strategy](#16-deployment-strategy)
17. [Monetization Strategy](#17-monetization-strategy)
18. [Success Criteria](#18-success-criteria)
19. [Timeline & Milestones](#19-timeline--milestones)
20. [Appendix](#20-appendix)

---

## **1. Executive Summary**

### **1.1 Product Vision**
ChatSupport AI is a SaaS platform that enables SMEs and startups to deploy intelligent AI-powered chatbots on their websites within minutes, reducing customer support costs by up to 70% while maintaining 24/7 availability and high-quality customer interactions.

### **1.2 Problem Statement**
- SMEs spend $15,000-$50,000 annually on customer support
- 68% of customers expect instant responses
- Small businesses can't afford 24/7 human support teams
- Existing solutions are too expensive or too complex

### **1.3 Solution**
A complete AI chatbot platform that:
- Deploys in 5 minutes with a simple code snippet
- Learns from company documentation automatically
- Handles 80% of common inquiries without human intervention
- Seamlessly transfers complex issues to human agents
- Costs 90% less than traditional support solutions

### **1.4 Target Market**
- **Primary**: B2B SaaS companies (10-100 employees)
- **Secondary**: E-commerce stores, Service businesses
- **Geographic**: Global, English-speaking markets first
- **Market Size**: $12B addressable market (AI customer service)

### **1.5 Key Metrics**
- Time to first chatbot: < 10 minutes
- AI resolution rate: > 75%
- Customer satisfaction: > 4.5/5
- Response time: < 2 seconds
- Monthly churn: < 5%

---

## **2. Product Overview**

### **2.1 Product Description**
ChatSupport AI is a multi-tenant SaaS platform consisting of three core applications:

1. **Admin Dashboard** - Web application for business owners to manage chatbots
2. **Chat Widget** - Embeddable widget for end-customer interactions
3. **Mobile App** (Future) - Agent mobile app for on-the-go support

### **2.2 Core Value Propositions**

| **Feature** | **Value** | **Differentiation** |
|-------------|-----------|---------------------|
| Instant Setup | Deploy in 5 minutes | vs. 2-4 weeks for competitors |
| Smart Learning | Auto-trains from documents | vs. manual FAQ building |
| Hybrid AI+Human | Seamless handoff | vs. AI-only or human-only |
| Affordable Pricing | Starting at $29/month | vs. $200+ competitors |
| Multi-language | 50+ languages | vs. English-only solutions |

### **2.3 Product Principles**
1. **Simplicity First** - Complex AI made simple for non-technical users
2. **Performance** - Sub-2-second response times always
3. **Transparency** - Clear AI vs human distinction
4. **Privacy** - Customer data encrypted and isolated
5. **Continuous Learning** - AI improves with every conversation

---

## **3. User Personas**

### **3.1 Primary Persona: Sarah - Small Business Owner**

**Demographics:**
- Age: 32-45
- Role: Founder/CEO of 15-person SaaS startup
- Technical Level: Medium (can install WordPress plugins)
- Location: Urban areas, US/EU

**Goals:**
- Reduce support ticket volume
- Provide 24/7 customer support
- Scale support without hiring
- Improve customer satisfaction

**Pain Points:**
- Can't afford full support team
- Loses customers due to slow responses
- Spends 10+ hours/week on support emails
- Current chatbots are dumb and frustrating

**Needs:**
- Easy setup without developers
- Intelligent responses that sound human
- Ability to take over when needed
- Clear ROI metrics

**User Scenario:**
Sarah launches her SaaS product and gets 50 support emails daily. She can't afford a full-time support agent yet. She needs a solution that handles common questions automatically but can escalate technical issues to her or her technical co-founder.

---

### **3.2 Secondary Persona: Mike - Support Agent**

**Demographics:**
- Age: 24-35
- Role: Customer Support Representative
- Technical Level: Low-Medium
- Location: Any (remote worker)

**Goals:**
- Handle only complex customer issues
- Reduce repetitive questions
- Maintain high satisfaction scores
- Work efficiently with AI assistance

**Pain Points:**
- Answers same questions 50 times daily
- Feels overwhelmed by ticket volume
- Lacks context on customer history
- Can't access knowledge base quickly

**Needs:**
- Dashboard showing only escalated issues
- Full customer context when taking over
- Quick access to company knowledge
- Performance metrics and feedback

**User Scenario:**
Mike joins a growing startup as their second support agent. He uses ChatSupport AI to monitor ongoing conversations, only stepping in when the AI can't handle complex technical questions. The AI shows him full conversation context and suggests relevant knowledge base articles.

---

### **3.3 Tertiary Persona: Emma - End Customer**

**Demographics:**
- Age: 25-55
- Role: Customer/User of a product
- Technical Level: Varies
- Location: Any

**Goals:**
- Get quick answers to questions
- Resolve issues without waiting
- Feel heard and understood
- Have option to talk to humans

**Pain Points:**
- Long wait times for support
- Dumb chatbots that don't understand
- No support outside business hours
- Has to repeat information multiple times

**Needs:**
- Instant responses 24/7
- Natural conversation experience
- Clear escalation path to humans
- Conversation history saved

**User Scenario:**
Emma has a question about her subscription renewal at 11 PM. She clicks the chat widget, asks her question, and gets an accurate answer in 3 seconds with links to relevant documentation. For a complex billing issue, the AI smoothly transfers her to an agent the next morning with full context.

---

## **4. User Stories**

### **4.1 Epic 1: Account Setup & Onboarding**

#### **Story 1.1: Workspace Creation**
**As a** business owner  
**I want to** create my workspace with my company branding  
**So that** I can start using the platform with my brand identity

**Acceptance Criteria:**
- [ ] User can sign up with email/password or Google OAuth
- [ ] User enters company name, website URL, and industry
- [ ] User uploads company logo (PNG/JPG, max 2MB)
- [ ] User selects brand colors (primary, secondary)
- [ ] Workspace is created within 5 seconds
- [ ] User receives welcome email with setup guide
- [ ] User is redirected to dashboard homepage

**Technical Requirements:**
- Email validation and duplicate checking
- Image upload to S3/CloudFront with optimization
- Multi-tenant database record creation
- JWT token generation for authentication
- SendGrid email trigger

**Priority:** P0 (Must Have)  
**Effort:** 5 Story Points  
**Dependencies:** None

---

#### **Story 1.2: First Chatbot Creation**
**As a** business owner  
**I want to** create my first chatbot in under 5 minutes  
**So that** I can quickly start providing automated support

**Acceptance Criteria:**
- [ ] User clicks "Create Chatbot" button on dashboard
- [ ] User enters chatbot name (e.g., "Support Bot")
- [ ] User selects chatbot personality (Professional, Friendly, Casual)
- [ ] User sets business hours or 24/7 availability
- [ ] User customizes welcome message
- [ ] User gets installation code snippet
- [ ] Chatbot is created and active immediately
- [ ] User can preview chatbot in modal

**Technical Requirements:**
- Form validation for required fields
- Real-time preview using React state
- Generate unique chatbot ID (UUID)
- Create default configuration in database
- Generate embed code with chatbot ID
- Copy-to-clipboard functionality

**Priority:** P0 (Must Have)  
**Effort:** 8 Story Points  
**Dependencies:** Story 1.1

---

#### **Story 1.3: Knowledge Base Upload**
**As a** business owner  
**I want to** upload my company documentation  
**So that** the AI can answer questions accurately

**Acceptance Criteria:**
- [ ] User can upload multiple files (PDF, DOCX, TXT) up to 10MB each
- [ ] User can add URLs to crawl (FAQs, documentation pages)
- [ ] User sees upload progress bar for each file
- [ ] User sees processing status (processing, completed, failed)
- [ ] User receives notification when processing is complete
- [ ] User can test knowledge with sample questions
- [ ] User can delete or re-upload documents

**Technical Requirements:**
- Multipart file upload to S3
- Background job queue (Bull/BullMQ) for processing
- Text extraction using pdf-parse, mammoth (DOCX)
- Web scraping using Puppeteer/Cheerio for URLs
- Text chunking (500-1000 tokens per chunk)
- Vector embedding generation using OpenAI Embeddings
- Storage in Pinecone/Weaviate vector database
- WebSocket updates for processing status

**Priority:** P0 (Must Have)  
**Effort:** 13 Story Points  
**Dependencies:** Story 1.2

---

### **4.2 Epic 2: Chat Widget Integration**

#### **Story 2.1: Widget Installation**
**As a** business owner  
**I want to** install the chat widget on my website  
**So that** customers can start using it immediately

**Acceptance Criteria:**
- [ ] User copies installation code from dashboard
- [ ] User pastes code before closing </body> tag
- [ ] Widget appears on website within 30 seconds
- [ ] Widget is positioned bottom-right by default
- [ ] Widget loads without affecting page performance
- [ ] Widget is responsive on mobile devices
- [ ] User sees confirmation in dashboard when widget is detected

**Technical Requirements:**
- Generate JavaScript snippet with chatbot ID
- Async script loading to prevent blocking
- Create iframe for widget isolation
- PostMessage API for cross-origin communication
- CSS scoping to prevent style conflicts
- Mobile breakpoint detection
- Webhook ping to backend when widget loads

**Priority:** P0 (Must Have)  
**Effort:** 8 Story Points  
**Dependencies:** Story 1.2

---

#### **Story 2.2: Widget Customization**
**As a** business owner  
**I want to** customize the widget appearance  
**So that** it matches my website design

**Acceptance Criteria:**
- [ ] User can change widget position (bottom-right, bottom-left, custom)
- [ ] User can customize colors (background, text, buttons)
- [ ] User can upload custom avatar for bot
- [ ] User can set widget size (small, medium, large)
- [ ] User can customize bubble button text/icon
- [ ] Changes are reflected in real-time preview
- [ ] Changes apply to live widget within 10 seconds

**Technical Requirements:**
- Configuration stored in database
- CSS variables for dynamic theming
- CDN cache invalidation on updates
- WebSocket push for live config updates
- Image optimization for avatars
- Fallback to default values if config fails

**Priority:** P1 (Should Have)  
**Effort:** 5 Story Points  
**Dependencies:** Story 2.1

---

### **4.3 Epic 3: Conversation Management**

#### **Story 3.1: Real-time Conversation Monitoring**
**As a** support agent  
**I want to** see all active conversations in real-time  
**So that** I can monitor AI performance and step in when needed

**Acceptance Criteria:**
- [ ] Agent sees list of all active conversations
- [ ] List updates in real-time as messages arrive
- [ ] Each conversation shows customer name, last message, timestamp
- [ ] Agent can filter by status (active, waiting, resolved)
- [ ] Agent can search conversations by customer or keyword
- [ ] Agent can click conversation to view full thread
- [ ] Unread messages are highlighted
- [ ] Agent receives browser notification for escalations

**Technical Requirements:**
- WebSocket connection for real-time updates
- React Query for data fetching and caching
- Virtual scrolling for performance (react-window)
- Debounced search input
- Browser Notification API integration
- Redis pub/sub for multi-server scaling
- Optimistic UI updates

**Priority:** P0 (Must Have)  
**Effort:** 13 Story Points  
**Dependencies:** Story 1.1

---

#### **Story 3.2: Agent Takeover**
**As a** support agent  
**I want to** take over conversations from AI  
**So that** I can handle complex issues personally

**Acceptance Criteria:**
- [ ] Agent clicks "Take Over" button in conversation
- [ ] Customer is notified "You're now chatting with [Agent Name]"
- [ ] Agent sees full conversation history
- [ ] Agent sees customer context (email, previous conversations, metadata)
- [ ] Agent can type and send messages
- [ ] AI stops responding automatically
- [ ] Agent can return conversation to AI
- [ ] Takeover is logged in conversation history

**Technical Requirements:**
- Conversation state management (ai_handling, agent_handling)
- WebSocket event: agent_takeover
- Update UI for both agent and customer
- Load customer profile from database
- Lock conversation for single agent (prevent conflicts)
- Audit log entry for compliance
- Typing indicator for agent

**Priority:** P0 (Must Have)  
**Effort:** 8 Story Points  
**Dependencies:** Story 3.1

---

#### **Story 3.3: Conversation History & Search**
**As a** support agent  
**I want to** search through past conversations  
**So that** I can find relevant information quickly

**Acceptance Criteria:**
- [ ] Agent can view all past conversations
- [ ] Agent can search by customer name, email, or message content
- [ ] Agent can filter by date range, status, or assigned agent
- [ ] Search returns results within 2 seconds
- [ ] Agent can export conversation transcript
- [ ] Agent can add internal notes to conversations
- [ ] Agent can tag conversations for organization

**Technical Requirements:**
- Elasticsearch for full-text search
- Indexed fields: customer_name, email, message_content, tags
- Pagination for large result sets
- Export to PDF using jsPDF
- Internal notes stored separately from messages
- Tag system with autocomplete
- Date range picker component

**Priority:** P1 (Should Have)  
**Effort:** 8 Story Points  
**Dependencies:** Story 3.1

---

### **4.4 Epic 4: AI Intelligence**

#### **Story 4.1: Contextual AI Responses**
**As an** end customer  
**I want to** receive accurate answers to my questions  
**So that** I can resolve my issues quickly

**Acceptance Criteria:**
- [ ] Customer types question in chat widget
- [ ] AI responds within 2 seconds
- [ ] Response is relevant to the question
- [ ] Response includes links to documentation when applicable
- [ ] AI maintains conversation context across multiple messages
- [ ] AI admits when it doesn't know the answer
- [ ] AI suggests escalation for complex issues

**Technical Requirements:**
- Message sent to backend via WebSocket
- Vector similarity search in knowledge base (Pinecone/Weaviate)
- Retrieve top 3-5 relevant chunks
- Construct prompt with context and conversation history
- Call OpenAI/Claude API with streaming
- Stream response back to frontend via WebSocket
- Store message in database
- Track response time metrics

**Prompt Template:**
```
You are a helpful customer support assistant for {company_name}.

Context from knowledge base:
{retrieved_chunks}

Conversation history:
{last_5_messages}

Customer question: {user_message}

Provide a helpful, accurate answer. If you're not certain, say so and suggest speaking with a human agent. Include relevant links when available.
```

**Priority:** P0 (Must Have)  
**Effort:** 13 Story Points  
**Dependencies:** Story 1.3, Story 2.1

---

#### **Story 4.2: Smart Escalation Detection**
**As an** AI system  
**I want to** detect when to escalate to human agents  
**So that** customers get appropriate help

**Acceptance Criteria:**
- [ ] AI detects frustrated customers (keywords: angry, upset, terrible)
- [ ] AI detects complex technical questions beyond knowledge base
- [ ] AI detects billing/refund requests
- [ ] AI detects requests to speak with humans
- [ ] AI proactively offers human escalation after 3 unsuccessful responses
- [ ] Escalation triggers agent notification
- [ ] Customer is informed about escalation clearly

**Technical Requirements:**
- Sentiment analysis on customer messages
- Confidence score from vector search (< 0.7 = escalate)
- Business rule engine for escalation triggers
- Category detection (billing, technical, complaint)
- Escalation counter in conversation state
- WebSocket event to notify available agents
- Queue system if no agents available

**Priority:** P0 (Must Have)  
**Effort:** 8 Story Points  
**Dependencies:** Story 4.1

---

#### **Story 4.3: Continuous Learning**
**As a** business owner  
**I want to** improve AI responses over time  
**So that** accuracy increases with usage

**Acceptance Criteria:**
- [ ] Owner reviews AI responses in dashboard
- [ ] Owner can mark responses as correct/incorrect
- [ ] Owner can edit and save better responses
- [ ] Improved responses are used for future questions
- [ ] System suggests knowledge gaps based on failed queries
- [ ] Analytics show AI improvement over time
- [ ] Automatic retraining weekly

**Technical Requirements:**
- Feedback buttons on each AI response
- Store feedback in analytics database
- Create feedback loop for model fine-tuning
- Query analytics to identify common failed questions
- Suggest new knowledge base articles
- Fine-tuning pipeline using OpenAI fine-tune API
- A/B testing framework for response variations

**Priority:** P2 (Nice to Have)  
**Effort:** 13 Story Points  
**Dependencies:** Story 4.1, Story 5.1

---

### **4.5 Epic 5: Analytics & Reporting**

#### **Story 5.1: Dashboard Metrics**
**As a** business owner  
**I want to** see key performance metrics  
**So that** I can measure the impact of the chatbot

**Acceptance Criteria:**
- [ ] Dashboard shows total conversations (today, this week, this month)
- [ ] Dashboard shows AI resolution rate (% resolved without human)
- [ ] Dashboard shows average response time
- [ ] Dashboard shows customer satisfaction score
- [ ] Dashboard shows top 5 common questions
- [ ] Metrics update in real-time
- [ ] User can export reports as PDF

**Technical Requirements:**
- Aggregation queries on PostgreSQL/MongoDB
- Redis caching for frequently accessed metrics
- Chart.js or Recharts for visualizations
- Real-time updates via WebSocket
- Background job for report generation
- PDF export using Puppeteer
- Time zone handling for accurate reporting

**Priority:** P1 (Should Have)  
**Effort:** 8 Story Points  
**Dependencies:** Story 3.1

---

#### **Story 5.2: Conversation Analytics**
**As a** business owner  
**I want to** analyze conversation trends  
**So that** I can identify improvement opportunities

**Acceptance Criteria:**
- [ ] Owner sees conversation volume over time (line chart)
- [ ] Owner sees peak hours for conversations
- [ ] Owner sees most asked questions (word cloud)
- [ ] Owner sees customer sentiment trend
- [ ] Owner sees agent performance metrics
- [ ] Owner can filter by date range or chatbot
- [ ] Owner can drill down into specific metrics

**Technical Requirements:**
- Time-series data aggregation
- Natural language processing for question clustering
- Sentiment analysis on all messages
- Agent performance calculations (avg. resolution time, CSAT)
- Interactive charts with drill-down
- Data warehouse for historical analytics (BigQuery/Redshift)
- Scheduled jobs for daily aggregations

**Priority:** P1 (Should Have)  
**Effort:** 13 Story Points  
**Dependencies:** Story 5.1

---

### **4.6 Epic 6: Team Collaboration**

#### **Story 6.1: Team Member Management**
**As a** business owner  
**I want to** invite team members  
**So that** multiple agents can handle support

**Acceptance Criteria:**
- [ ] Owner can invite members via email
- [ ] Invited members receive invitation email
- [ ] Members can accept and create accounts
- [ ] Owner can assign roles (Admin, Agent, Viewer)
- [ ] Owner can deactivate members
- [ ] Members see only permitted features based on role
- [ ] Audit log tracks all team actions

**Technical Requirements:**
- Invitation token generation (expires in 7 days)
- Email sending via SendGrid
- Role-based access control (RBAC) middleware
- Permission matrix in database
- Account activation flow
- Audit logging system
- Team member listing with filters

**Priority:** P1 (Should Have)  
**Effort:** 8 Story Points  
**Dependencies:** Story 1.1

---

#### **Story 6.2: Internal Notes & Collaboration**
**As a** support agent  
**I want to** add internal notes to conversations  
**So that** team members have context

**Acceptance Criteria:**
- [ ] Agent can add internal notes to any conversation
- [ ] Notes are visible only to team members, not customers
- [ ] Notes show author and timestamp
- [ ] Agent can @mention team members in notes
- [ ] Mentioned members receive notification
- [ ] Notes are searchable
- [ ] Notes appear in conversation timeline

**Technical Requirements:**
- Separate notes table linked to conversations
- @mention parsing and user lookup
- Real-time notifications via WebSocket
- Markdown support for rich formatting
- Search indexing in Elasticsearch
- Timeline component showing messages and notes

**Priority:** P2 (Nice to Have)  
**Effort:** 5 Story Points  
**Dependencies:** Story 3.1, Story 6.1

---

### **4.7 Epic 7: Integrations**

#### **Story 7.1: Email Integration**
**As a** business owner  
**I want to** receive chat transcripts via email  
**So that** I have a record of conversations

**Acceptance Criteria:**
- [ ] User can configure email notifications in settings
- [ ] Email sent when conversation is resolved
- [ ] Email includes full conversation transcript
- [ ] Email includes customer information
- [ ] Email includes satisfaction rating if provided
- [ ] User can reply to email to reopen conversation
- [ ] User can disable email notifications

**Technical Requirements:**
- Email template design (HTML + plain text)
- SendGrid integration for sending
- Inbound email parsing for replies
- Email-to-conversation mapping via reference ID
- User preference storage
- Background job for email sending
- Retry logic for failed deliveries

**Priority:** P1 (Should Have)  
**Effort:** 5 Story Points  
**Dependencies:** Story 3.1

---

#### **Story 7.2: Slack Integration**
**As a** support agent  
**I want to** receive notifications in Slack  
**So that** I can respond without checking dashboard

**Acceptance Criteria:**
- [ ] User connects Slack workspace via OAuth
- [ ] User selects Slack channel for notifications
- [ ] Agent receives Slack message for new conversations
- [ ] Agent receives Slack message for escalations
- [ ] Slack message includes conversation link
- [ ] Agent can mark as resolved from Slack
- [ ] User can disconnect Slack integration

**Technical Requirements:**
- Slack OAuth 2.0 flow
- Store workspace tokens securely
- Slack API client for sending messages
- Interactive message buttons
- Webhook for button actions
- Token refresh handling
- Integration settings UI

**Priority:** P2 (Nice to Have)  
**Effort:** 8 Story Points  
**Dependencies:** Story 3.1

---

#### **Story 7.3: CRM Integration (Salesforce/HubSpot)**
**As a** business owner  
**I want to** sync conversations to my CRM  
**So that** I have unified customer data

**Acceptance Criteria:**
- [ ] User connects CRM via OAuth
- [ ] Conversations sync as CRM tickets/activities
- [ ] Customer information syncs bidirectionally
- [ ] Tags sync as CRM properties
- [ ] Sync happens in real-time or scheduled
- [ ] User can configure field mapping
- [ ] User can disconnect CRM integration

**Technical Requirements:**
- OAuth 2.0 for Salesforce/HubSpot
- API clients for each CRM
- Field mapping configuration UI
- Webhook listeners for CRM updates
- Background sync jobs
- Conflict resolution logic
- Error handling and retry

**Priority:** P2 (Nice to Have)  
**Effort:** 21 Story Points  
**Dependencies:** Story 3.1

---

## **5. Functional Requirements**

### **5.1 Authentication & Authorization**

#### **5.1.1 User Authentication**

**Requirements:**
- Email/password authentication with bcrypt hashing (salt rounds: 12)
- Google OAuth 2.0 integration
- JWT token-based authentication (access token + refresh token)
- Access token expiry: 15 minutes
- Refresh token expiry: 30 days
- Password requirements: minimum 8 characters, 1 uppercase, 1 number, 1 special character
- Account email verification required before access
- Password reset via email with expiring token (valid for 1 hour)
- Multi-factor authentication (MFA) via TOTP (optional, enterprise only)
- Session management: maximum 5 active sessions per user
- Automatic logout after 30 days of inactivity

**Technical Implementation:**
```typescript
// Authentication Flow
1. User submits credentials
2. Backend validates credentials
3. Generate JWT access token (15min) + refresh token (30d)
4. Return tokens + user object
5. Frontend stores access token in memory
6. Frontend stores refresh token in httpOnly cookie
7. API requests include access token in Authorization header
8. Refresh token endpoint exchanges expired access token

// Token Structure
{
  "access_token": "eyJhbGc...",
  "refresh_token": "dGVzdC...",
  "expires_in": 900,
  "token_type": "Bearer",
  "user": {
    "id": "uuid",
    "email": "user@example.com",
    "workspace_id": "workspace_uuid",
    "role": "admin"
  }
}
```

**Endpoints:**
- `POST /api/v1/auth/register` - Create new account
- `POST /api/v1/auth/login` - Email/password login
- `POST /api/v1/auth/google` - Google OAuth callback
- `POST /api/v1/auth/refresh` - Refresh access token
- `POST /api/v1/auth/logout` - Invalidate tokens
- `POST /api/v1/auth/forgot-password` - Request password reset
- `POST /api/v1/auth/reset-password` - Reset password with token
- `POST /api/v1/auth/verify-email` - Verify email address
- `GET /api/v1/auth/me` - Get current user info

---

#### **5.1.2 Role-Based Access Control (RBAC)**

**Roles:**

| **Role** | **Permissions** | **Description** |
|----------|----------------|----------------|
| **Super Admin** | All permissions | Platform owner, manages multiple workspaces |
| **Owner** | Full workspace access | Created the workspace, billing admin |
| **Admin** | Manage team, chatbots, settings | Can't access billing |
| **Agent** | View/respond to conversations, knowledge base | Support team member |
| **Viewer** | Read-only access to analytics | Stakeholder, observer |

**Permission Matrix:**

| **Feature** | **Owner** | **Admin** | **Agent** | **Viewer** |
|-------------|-----------|-----------|-----------|------------|
| Manage Billing | ✅ | ❌ | ❌ | ❌ |
| Create/Delete Chatbots | ✅ | ✅ | ❌ | ❌ |
| Edit Chatbot Settings | ✅ | ✅ | ✅ | ❌ |
| Invite/Remove Team | ✅ | ✅ | ❌ | ❌ |
| View Conversations | ✅ | ✅ | ✅ | ✅ |
| Respond to Conversations | ✅ | ✅ | ✅ | ❌ |
| Upload Knowledge Base | ✅ | ✅ | ✅ | ❌ |
| View Analytics | ✅ | ✅ | ✅ | ✅ |
| Export Data | ✅ | ✅ | ❌ | ❌ |
| API Access | ✅ | ✅ | ❌ | ❌ |

**Technical Implementation:**
```typescript
// Middleware: requirePermission
const requirePermission = (permission: Permission) => {
  return async (req, res, next) => {
    const user = req.user; // From JWT
    const hasPermission = await checkPermission(
      user.role, 
      permission
    );
    
    if (!hasPermission) {
      return res.status(403).json({ 
        error: 'Insufficient permissions' 
      });
    }
    next();
  };
};

// Usage
router.delete(
  '/chatbots/:id',
  authenticate,
  requirePermission('chatbot.delete'),
  deleteChatbot
);
```

---

### **5.2 Chatbot Management**

#### **5.2.1 Chatbot CRUD Operations**

**Create Chatbot:**
```typescript
// Request
POST /api/v1/chatbots
{
  "name": "Support Bot",
  "personality": "professional", // professional, friendly, casual
  "greeting_message": "Hi! How can I help you today?",
  "business_hours": {
    "enabled": true,
    "timezone": "America/New_York",
    "schedule": {
      "monday": { "start": "09:00", "end": "17:00" },
      "tuesday": { "start": "09:00", "end": "17:00" },
      // ... other days
    },
    "offline_message": "We're currently offline. Leave a message!"
  },
  "ai_settings": {
    "model": "gpt-4o-mini", // gpt-4o-mini, claude-sonnet-4.5
    "temperature": 0.7,
    "max_tokens": 500,
    "enable_escalation": true,
    "escalation_triggers": {
      "low_confidence_threshold": 0.7,
      "frustration_detection": true,
      "explicit_request": true,
      "max_failed_responses": 3
    }
  },
  "widget_settings": {
    "position": "bottom-right", // bottom-right, bottom-left
    "theme": {
      "primary_color": "#3B82F6",
      "secondary_color": "#1E40AF",
      "text_color": "#FFFFFF",
      "background_color": "#FFFFFF"
    },
    "avatar_url": "https://cdn.example.com/avatar.png",
    "button_text": "Chat with us",
    "size": "medium" // small, medium, large
  }
}

// Response
{
  "id": "chatbot_abc123",
  "workspace_id": "workspace_xyz",
  "name": "Support Bot",
  "status": "active",
  "created_at": "2025-10-05T10:30:00Z",
  "embed_code": "<script>...</script>",
  // ... other fields
}
```

**Features:**
- Unlimited chatbots for Pro/Enterprise plans
- Clone existing chatbot configuration
- Archive/restore chatbots (soft delete)
- Duplicate detection by name
- Version history for configuration changes
- A/B testing between chatbot configurations

#### **5.2.2 Chatbot Configuration**

**Update Chatbot:**
```typescript
// Request
PATCH /api/v1/chatbots/:id
{
  "name": "Updated Support Bot",
  "ai_settings": {
    "temperature": 0.8,
    "enable_fallback_responses": true,
    "fallback_responses": [
      "I'm not sure about that. Let me connect you with a team member.",
      "That's a great question. Would you like to speak with someone from our team?"
    ]
  },
  "data_collection": {
    "collect_email": true,
    "collect_name": true,
    "custom_fields": [
      {
        "name": "company",
        "type": "text",
        "required": false,
        "label": "Company Name"
      }
    ]
  },
  "behavior_settings": {
    "response_delay": 1000, // milliseconds to simulate typing
    "show_typing_indicator": true,
    "enable_rich_responses": true, // buttons, cards, images
    "max_conversation_length": 50, // messages before suggesting escalation
    "inactivity_timeout": 300000 // 5 minutes
  }
}

// Response
{
  "id": "chatbot_abc123",
  "updated_at": "2025-10-05T11:00:00Z",
  "version": 2,
  // ... updated fields
}
```

**Validation Rules:**
- Name: 3-50 characters, alphanumeric + spaces
- Temperature: 0.0-2.0
- Max tokens: 100-2000
- Colors: valid hex codes
- Avatar: max 2MB, PNG/JPG only
- Response delay: 0-5000ms
- Inactivity timeout: 60000-1800000ms (1-30 minutes)

---

#### **5.2.3 Chatbot Analytics Per Bot**

**Get Chatbot Analytics:**
```typescript
// Request
GET /api/v1/chatbots/:id/analytics?period=7d

// Response
{
  "chatbot_id": "chatbot_abc123",
  "period": {
    "start": "2025-09-28T00:00:00Z",
    "end": "2025-10-05T23:59:59Z"
  },
  "metrics": {
    "total_conversations": 1547,
    "total_messages": 8235,
    "ai_resolved": 1236, // 79.9%
    "human_escalated": 311, // 20.1%
    "avg_response_time_ms": 1843,
    "avg_conversation_length": 5.3, // messages
    "avg_resolution_time_minutes": 4.2,
    "satisfaction_score": 4.6, // out of 5
    "total_satisfaction_responses": 892
  },
  "top_intents": [
    {
      "intent": "billing_question",
      "count": 342,
      "percentage": 22.1,
      "avg_confidence": 0.89
    },
    {
      "intent": "technical_support",
      "count": 289,
      "percentage": 18.7,
      "avg_confidence": 0.76
    }
    // ... more intents
  ],
  "hourly_distribution": [
    { "hour": 0, "conversations": 12 },
    { "hour": 1, "conversations": 8 },
    // ... 24 hours
  ],
  "daily_trend": [
    { "date": "2025-09-28", "conversations": 198, "ai_resolution_rate": 0.78 },
    { "date": "2025-09-29", "conversations": 224, "ai_resolution_rate": 0.81 },
    // ... 7 days
  ],
  "common_questions": [
    {
      "question": "How do I reset my password?",
      "count": 67,
      "avg_confidence": 0.92,
      "resolution_rate": 0.95
    }
    // ... top 10
  ],
  "escalation_reasons": [
    { "reason": "low_confidence", "count": 156 },
    { "reason": "user_request", "count": 89 },
    { "reason": "frustration_detected", "count": 43 },
    { "reason": "max_failed_responses", "count": 23 }
  ]
}
```

**Period Options:**
- `24h` - Last 24 hours
- `7d` - Last 7 days (default)
- `30d` - Last 30 days
- `90d` - Last 90 days
- `custom` - Custom date range with `start_date` and `end_date` params

---

### **5.3 Conversation Management**

#### **5.3.1 Conversation Lifecycle**

**States:**
```typescript
enum ConversationStatus {
  ACTIVE = 'active',           // Ongoing conversation
  WAITING = 'waiting',         // Waiting for customer response
  ESCALATED = 'escalated',     // Escalated to human agent
  AGENT_HANDLING = 'agent_handling', // Agent actively responding
  RESOLVED = 'resolved',       // Conversation completed
  ABANDONED = 'abandoned',     // Customer left without resolution
  ARCHIVED = 'archived'        // Moved to archive
}
```

**State Transitions:**
```typescript
// Valid state transitions
ACTIVE -> WAITING (after AI response)
ACTIVE -> ESCALATED (escalation triggered)
WAITING -> ACTIVE (customer responds)
WAITING -> ABANDONED (inactivity timeout)
ESCALATED -> AGENT_HANDLING (agent accepts)
AGENT_HANDLING -> RESOLVED (agent closes)
AGENT_HANDLING -> ACTIVE (returned to AI)
RESOLVED -> ACTIVE (customer reopens)
* -> ARCHIVED (manual archive after 90 days)
```

**Automatic State Management:**
- `WAITING` → `ABANDONED`: After inactivity_timeout (default 5 minutes)
- `ACTIVE` → `ESCALATED`: When escalation triggers fire
- `RESOLVED` → `ARCHIVED`: After 90 days (configurable)
- `AGENT_HANDLING` → `WAITING`: If agent doesn't respond in 10 minutes

---

#### **5.3.2 Real-time Conversation API**

**WebSocket Connection:**
```typescript
// Client connection
const socket = io('wss://api.chatsupport.ai', {
  auth: {
    token: accessToken,
    chatbot_id: 'chatbot_abc123',
    visitor_id: 'visitor_xyz' // or customer_id for returning
  },
  transports: ['websocket']
});

// Event: New message from customer
socket.emit('message:send', {
  conversation_id: 'conv_123', // null for new conversation
  content: 'How do I reset my password?',
  attachments: [], // optional
  metadata: {
    page_url: 'https://example.com/pricing',
    user_agent: 'Mozilla/5.0...',
    referrer: 'https://google.com'
  }
});

// Event: AI response
socket.on('message:receive', (data) => {
  {
    "message_id": "msg_abc",
    "conversation_id": "conv_123",
    "content": "To reset your password, click on 'Forgot Password' on the login page...",
    "sender": {
      "type": "bot",
      "name": "Support Bot",
      "avatar_url": "https://cdn.example.com/bot-avatar.png"
    },
    "timestamp": "2025-10-05T14:30:45Z",
    "confidence_score": 0.92,
    "sources": [
      {
        "title": "Password Reset Guide",
        "url": "https://docs.example.com/password-reset",
        "snippet": "Follow these steps to reset..."
      }
    ],
    "suggested_actions": [
      {
        "type": "button",
        "label": "Reset Password",
        "action": "open_url",
        "value": "https://example.com/reset-password"
      }
    ]
  }
});

// Event: Typing indicator
socket.on('typing:start', (data) => {
  {
    "conversation_id": "conv_123",
    "sender": { "type": "bot" | "agent", "name": "Agent Name" }
  }
});

socket.on('typing:stop', (data) => {
  // Same structure
});

// Event: Escalation notification
socket.on('conversation:escalated', (data) => {
  {
    "conversation_id": "conv_123",
    "reason": "low_confidence",
    "message": "Let me connect you with a team member who can help better.",
    "estimated_wait_time": 120 // seconds
  }
});

// Event: Agent joined
socket.on('agent:joined', (data) => {
  {
    "conversation_id": "conv_123",
    "agent": {
      "id": "agent_xyz",
      "name": "Sarah Johnson",
      "avatar_url": "https://cdn.example.com/agent-avatar.png",
      "title": "Support Specialist"
    }
  }
});

// Event: Conversation status changed
socket.on('conversation:status_changed', (data) => {
  {
    "conversation_id": "conv_123",
    "old_status": "escalated",
    "new_status": "agent_handling",
    "changed_by": "agent_xyz"
  }
});
```

**Rate Limiting:**
- Maximum 10 messages per minute per visitor
- Maximum 100 messages per hour per visitor
- Maximum 1000 concurrent WebSocket connections per workspace (scales with plan)

---

#### **5.3.3 Conversation REST API**

**List Conversations:**
```typescript
// Request
GET /api/v1/conversations?status=active&page=1&limit=20&sort=-updated_at

// Response
{
  "data": [
    {
      "id": "conv_123",
      "chatbot_id": "chatbot_abc123",
      "customer": {
        "id": "customer_xyz",
        "name": "John Doe",
        "email": "john@example.com",
        "avatar_url": null,
        "metadata": {
          "company": "Acme Inc",
          "location": "San Francisco, CA"
        }
      },
      "status": "active",
      "assigned_agent": null,
      "message_count": 8,
      "last_message": {
        "content": "Thanks for your help!",
        "sender_type": "customer",
        "timestamp": "2025-10-05T14:35:22Z"
      },
      "created_at": "2025-10-05T14:28:10Z",
      "updated_at": "2025-10-05T14:35:22Z",
      "tags": ["billing", "urgent"],
      "satisfaction_rating": null,
      "context": {
        "page_url": "https://example.com/pricing",
        "referrer": "https://google.com/search?q=best+crm",
        "device": "desktop",
        "browser": "Chrome 118"
      }
    }
    // ... more conversations
  ],
  "pagination": {
    "page": 1,
    "limit": 20,
    "total": 487,
    "total_pages": 25
  }
}
```

**Get Single Conversation:**
```typescript
// Request
GET /api/v1/conversations/:id

// Response
{
  "id": "conv_123",
  "chatbot_id": "chatbot_abc123",
  "customer": { /* full customer object */ },
  "status": "resolved",
  "assigned_agent": {
    "id": "agent_xyz",
    "name": "Sarah Johnson",
    "email": "sarah@example.com"
  },
  "messages": [
    {
      "id": "msg_001",
      "content": "Hi! How can I help you today?",
      "sender": {
        "type": "bot",
        "id": "chatbot_abc123",
        "name": "Support Bot"
      },
      "timestamp": "2025-10-05T14:28:10Z",
      "metadata": {
        "confidence_score": null,
        "sources": []
      }
    },
    {
      "id": "msg_002",
      "content": "I need help resetting my password",
      "sender": {
        "type": "customer",
        "id": "customer_xyz",
        "name": "John Doe"
      },
      "timestamp": "2025-10-05T14:28:45Z",
      "metadata": {}
    },
    {
      "id": "msg_003",
      "content": "I can help you with that! To reset your password...",
      "sender": {
        "type": "bot",
        "id": "chatbot_abc123",
        "name": "Support Bot"
      },
      "timestamp": "2025-10-05T14:28:48Z",
      "metadata": {
        "confidence_score": 0.94,
        "sources": [
          {
            "title": "Password Reset Guide",
            "url": "https://docs.example.com/password-reset"
          }
        ],
        "processing_time_ms": 1847
      }
    }
    // ... more messages
  ],
  "internal_notes": [
    {
      "id": "note_001",
      "content": "Customer had trouble with 2FA. Helped disable it temporarily.",
      "author": {
        "id": "agent_xyz",
        "name": "Sarah Johnson"
      },
      "created_at": "2025-10-05T14:32:00Z"
    }
  ],
  "events": [
    {
      "type": "created",
      "timestamp": "2025-10-05T14:28:10Z"
    },
    {
      "type": "escalated",
      "reason": "user_request",
      "timestamp": "2025-10-05T14:30:00Z"
    },
    {
      "type": "agent_joined",
      "agent_id": "agent_xyz",
      "timestamp": "2025-10-05T14:30:15Z"
    },
    {
      "type": "resolved",
      "resolved_by": "agent_xyz",
      "timestamp": "2025-10-05T14:35:30Z"
    }
  ],
  "tags": ["billing", "password-reset"],
  "satisfaction_rating": {
    "score": 5,
    "comment": "Very helpful, thanks!",
    "rated_at": "2025-10-05T14:36:00Z"
  },
  "metadata": {
    "total_response_time_seconds": 442,
    "ai_messages": 4,
    "agent_messages": 3,
    "customer_messages": 5
  },
  "created_at": "2025-10-05T14:28:10Z",
  "updated_at": "2025-10-05T14:36:00Z",
  "resolved_at": "2025-10-05T14:35:30Z"
}
```

**Agent Takeover:**
```typescript
// Request
POST /api/v1/conversations/:id/takeover
{
  "agent_id": "agent_xyz",
  "message": "Hi! I'm Sarah from support. I'll help you with this." // optional
}

// Response
{
  "conversation_id": "conv_123",
  "status": "agent_handling",
  "assigned_agent": {
    "id": "agent_xyz",
    "name": "Sarah Johnson"
  },
  "takeover_at": "2025-10-05T14:30:15Z"
}

// WebSocket event broadcast to customer
socket.emit('agent:joined', { /* agent details */ });
```

**Return to AI:**
```typescript
// Request
POST /api/v1/conversations/:id/return-to-ai

// Response
{
  "conversation_id": "conv_123",
  "status": "active",
  "assigned_agent": null,
  "returned_at": "2025-10-05T14:40:00Z"
}
```

**Resolve Conversation:**
```typescript
// Request
POST /api/v1/conversations/:id/resolve
{
  "resolution_note": "Customer's issue was resolved. Password reset successful.",
  "tags": ["resolved", "password-reset"]
}

// Response
{
  "conversation_id": "conv_123",
  "status": "resolved",
  "resolved_by": "agent_xyz",
  "resolved_at": "2025-10-05T14:35:30Z"
}

// Triggers satisfaction survey to customer
socket.emit('conversation:resolved', {
  "conversation_id": "conv_123",
  "survey": {
    "question": "How would you rate your experience?",
    "type": "rating",
    "scale": 5
  }
});
```

---

#### **5.3.4 Conversation Search & Filtering**

**Advanced Search:**
```typescript
// Request
POST /api/v1/conversations/search
{
  "query": "password reset", // full-text search
  "filters": {
    "status": ["active", "escalated"],
    "chatbot_id": "chatbot_abc123",
    "assigned_agent": "agent_xyz",
    "tags": ["billing", "urgent"],
    "date_range": {
      "field": "created_at", // created_at, updated_at, resolved_at
      "start": "2025-10-01T00:00:00Z",
      "end": "2025-10-05T23:59:59Z"
    },
    "satisfaction_rating": {
      "min": 4,
      "max": 5
    },
    "customer_email": "john@example.com",
    "has_internal_notes": true
  },
  "sort": {
    "field": "updated_at",
    "order": "desc" // asc, desc
  },
  "page": 1,
  "limit": 20
}

// Response
{
  "data": [ /* conversation objects with highlighted matches */ ],
  "pagination": { /* pagination object */ },
  "aggregations": {
    "by_status": {
      "active": 145,
      "escalated": 23,
      "resolved": 1289
    },
    "by_agent": {
      "agent_xyz": 67,
      "agent_abc": 45,
      "unassigned": 56
    },
    "avg_satisfaction": 4.6
  }
}
```

**Saved Filters:**
```typescript
// Create saved filter
POST /api/v1/conversation-filters
{
  "name": "Urgent Billing Issues",
  "filters": {
    "tags": ["billing", "urgent"],
    "status": ["active", "escalated"]
  },
  "is_default": false
}

// List saved filters
GET /api/v1/conversation-filters

// Apply saved filter
GET /api/v1/conversations?filter_id=filter_abc123
```

---

### **5.4 Knowledge Base Management**

#### **5.4.1 Document Upload & Processing**

**Upload Document:**
```typescript
// Request (multipart/form-data)
POST /api/v1/knowledge-base/documents
Content-Type: multipart/form-data

{
  "file": <binary>,
  "chatbot_id": "chatbot_abc123",
  "metadata": {
    "title": "Getting Started Guide",
    "category": "documentation",
    "language": "en",
    "version": "1.0"
  }
}

// Response
{
  "id": "doc_xyz789",
  "chatbot_id": "chatbot_abc123",
  "filename": "getting-started.pdf",
  "size_bytes": 524288,
  "mime_type": "application/pdf",
  "status": "processing",
  "metadata": { /* same as request */ },
  "created_at": "2025-10-05T15:00:00Z",
  "processing_started_at": "2025-10-05T15:00:01Z"
}

// WebSocket status updates
socket.on('document:processing', (data) => {
  {
    "document_id": "doc_xyz789",
    "status": "extracting_text",
    "progress": 25
  }
});

socket.on('document:processed', (data) => {
  {
    "document_id": "doc_xyz789",
    "status": "completed",
    "chunks_created": 47,
    "embeddings_generated": 47,
    "processing_time_seconds": 23
  }
});

socket.on('document:failed', (data) => {
  {
    "document_id": "doc_xyz789",
    "status": "failed",
    "error": {
      "code": "EXTRACTION_FAILED",
      "message": "Unable to extract text from PDF. File may be corrupted."
    }
  }
});
```

**Processing Pipeline:**
```typescript
// Step-by-step processing
1. Upload to S3
   - Generate unique key: workspace_id/chatbot_id/doc_id/filename
   - Set ACL: private
   - Store URL in database

2. Extract Text
   - PDF: pdf-parse library
   - DOCX: mammoth library
   - TXT: direct read
   - HTML/URL: Puppeteer for scraping
   - Images: OCR using Tesseract.js

3. Text Preprocessing
   - Remove excessive whitespace
   - Fix encoding issues
   - Remove headers/footers/page numbers
   - Preserve structure (headings, lists)

4. Chunking Strategy
   - Semantic chunking (preserve paragraphs/sections)
   - Chunk size: 500-1000 tokens
   - Overlap: 100 tokens between chunks
   - Metadata per chunk: source, page, section

5. Generate Embeddings
   - Model: text-embedding-3-small (OpenAI)
   - Dimensions: 1536
   - Batch size: 100 chunks
   - Retry failed batches

6. Store in Vector Database
   - Pinecone index or Weaviate
   - Namespace: chatbot_id
   - Metadata: document_id, chunk_index, content, page, etc.

7. Update Document Status
   - Mark as "completed"
   - Store statistics
   - Trigger webhook if configured
```

**Supported Formats:**
- PDF (`.pdf`) - Max 50MB
- Word (`.docx`, `.doc`) - Max 25MB
- Text (`.txt`, `.md`) - Max 10MB
- Web Pages (URLs) - Auto-crawl up to 50 pages
- CSV (`.csv`) - For FAQ imports

---

#### **5.4.2 URL Crawling**

**Add URL Source:**
```typescript
// Request
POST /api/v1/knowledge-base/urls
{
  "chatbot_id": "chatbot_abc123",
  "url": "https://docs.example.com",
  "crawl_settings": {
    "max_pages": 50,
    "max_depth": 3,
    "include_patterns": ["/docs/*", "/help/*"],
    "exclude_patterns": ["/blog/*", "/changelog/*"],
    "follow_external_links": false,
    "respect_robots_txt": true
  },
  "schedule": {
    "enabled": true,
    "frequency": "weekly", // daily, weekly, monthly
    "day_of_week": "monday",
    "time": "02:00"
  }
}

// Response
{
  "id": "url_source_abc",
  "chatbot_id": "chatbot_abc123",
  "url": "https://docs.example.com",
  "status": "crawling",
  "crawl_job_id": "job_xyz123",
  "pages_found": 0,
  "pages_processed": 0,
  "created_at": "2025-10-05T15:10:00Z"
}

// Crawling process WebSocket updates
socket.on('crawl:progress', (data) => {
  {
    "source_id": "url_source_abc",
    "pages_found": 27,
    "pages_processed": 15,
    "current_url": "https://docs.example.com/getting-started"
  }
});

socket.on('crawl:completed', (data) => {
  {
    "source_id": "url_source_abc",
    "status": "completed",
    "pages_found": 42,
    "pages_processed": 42,
    "chunks_created": 189,
    "duration_seconds": 145
  }
});
```

**Crawling Algorithm:**
```typescript
// Breadth-first search
1. Start with seed URL
2. Extract all links on page
3. Filter links by include/exclude patterns
4. Add to queue if not visited and within max_depth
5. Process page (extract text, chunk, embed)
6. Repeat until queue empty or max_pages reached
7. Store sitemap for future incremental updates
```

---

#### **5.4.3 FAQ Builder**

**Create FAQ:**
```typescript
// Request
POST /api/v1/knowledge-base/faqs
{
  "chatbot_id": "chatbot_abc123",
  "question": "How do I reset my password?",
  "answer": "To reset your password:\n1. Go to the login page\n2. Click 'Forgot Password'\n3. Enter your email\n4. Check your inbox for reset link",
  "category": "account",
  "tags": ["password", "account", "security"],
  "priority": "high", // high, medium, low
  "metadata": {
    "related_docs": ["doc_xyz789"],
    "related_urls": ["https://example.com/reset-password"]
  }
}

// Response
{
  "id": "faq_abc123",
  "chatbot_id": "chatbot_abc123",
  "question": "How do I reset my password?",
  "answer": "...",
  "embedding_generated": true,
  "status": "active",
  "usage_count": 0, // increments when FAQ is used in responses
  "created_at": "2025-10-05T15:20:00Z"
}
```

**Bulk FAQ Import:**
```typescript
// CSV Format
question,answer,category,tags
"How do I reset my password?","To reset your password...","account","password,security"
"What is your refund policy?","We offer 30-day refunds...","billing","refund,policy"

// Request
POST /api/v1/knowledge-base/faqs/import
Content-Type: multipart/form-data

{
  "file": <csv_file>,
  "chatbot_id": "chatbot_abc123",
  "overwrite_existing": false
}

// Response
{
  "imported": 45,
  "skipped": 3,
  "errors": [
    {
      "row": 12,
      "error": "Duplicate question found"
    }
  ]
}
```

---

#### **5.4.4 Knowledge Base Testing**

**Test Query:**
```typescript
// Request
POST /api/v1/knowledge-base/test
{
  "chatbot_id": "chatbot_abc123",
  "query": "How do I upgrade my subscription?",
  "top_k": 5 // return top 5 matches
}

// Response
{
  "query": "How do I upgrade my subscription?",
  "results": [
    {
      "rank": 1,
      "score": 0.89,
      "source_type": "faq",
      "source_id": "faq_xyz",
      "content": "To upgrade your subscription, go to Settings > Billing...",
      "metadata": {
        "category": "billing",
        "tags": ["subscription", "upgrade"]
      }
    },
    {
      "rank": 2,
      "score": 0.84,
      "source_type": "document",
      "source_id": "doc_abc",
      "content": "Our pricing plans include Starter, Pro, and Enterprise...",
      "metadata": {
        "filename": "pricing-guide.pdf",
        "page": 3
      }
    },
    {
      "rank": 3,
      "score": 0.78,
      "source_type": "url",
      "source_id": "url_page_123",
      "content": "Upgrading is easy! Just follow these steps...",
      "metadata": {
        "url": "https://docs.example.com/upgrade",
        "title": "Upgrade Guide"
      }
    }
  ],
  "suggested_answer": "To upgrade your subscription, go to Settings > Billing and select your desired plan. You can upgrade at any time, and you'll be charged the prorated difference.",
  "confidence_score": 0.89
}
```

**Knowledge Gaps Report:**
```typescript
// Request
GET /api/v1/knowledge-base/gaps?chatbot_id=chatbot_abc123&period=30d

// Response
{
  "period": {
    "start": "2025-09-05T00:00:00Z",
    "end": "2025-10-05T23:59:59Z"
  },
  "total_queries": 3482,
  "low_confidence_queries": 412, // confidence < 0.7
  "no_result_queries": 87,
  "gaps": [
    {
      "query_pattern": "mobile app",
      "count": 34,
      "avg_confidence": 0.52,
      "sample_queries": [
        "Do you have a mobile app?",
        "When will mobile app be released?",
        "Is there an iOS app?"
      ],
      "suggested_action": "Add FAQ or documentation about mobile app availability"
    },
    {
      "query_pattern": "api rate limits",
      "count": 28,
      "avg_confidence": 0.61,
      "sample_queries": [
        "What are the API rate limits?",
        "How many API calls can I make?",
        "API throttling limits"
      ],
      "suggested_action": "Add API documentation with rate limit details"
    }
  ]
}
```

---

### **5.5 AI Engine & Response Generation**

#### **5.5.1 AI Model Selection**

**Supported Models:**

| **Model** | **Provider** | **Cost/1K Tokens** | **Speed** | **Quality** | **Use Case** |
|-----------|--------------|-------------------|-----------|-------------|--------------|
| gpt-4o-mini | OpenAI | $0.00015 | Fast | Good | Default, cost-effective |
| gpt-4o | OpenAI | $0.0050 | Medium | Excellent | Complex queries |
| claude-sonnet-4.5 | Anthropic | $0.003 | Fast | Excellent | Balanced performance |
| claude-opus-4 | Anthropic | $0.015 | Slower | Best | Enterprise, accuracy critical |

**Model Selection Strategy:**
```typescript
// Automatic model selection based on query complexity
function selectModel(query: string, context: ConversationContext): Model {
  const complexity = analyzeQueryComplexity(query);
  const plan = context.workspace.subscription_plan;
  
  if (plan === 'starter') {
    return 'gpt-4o-mini'; // Forced for starter plan
  }
  
  if (complexity === 'simple' && plan === 'professional') {
    return 'gpt-4o-mini'; // Cost optimization
  }
  
  if (complexity === 'complex' || context.has_escalated) {
    return plan === 'enterprise' ? 'claude-opus-4' : 'gpt-4o';
  }
  
  return 'claude-sonnet-4.5'; // Default for pro/enterprise
}
```

---

#### **5.5.2 Prompt Engineering**

**System Prompt Template:**
```typescript
const systemPrompt = `You are an AI customer support assistant for ${company_name}.

IDENTITY:
Company: ${company_name}
- Your name: ${chatbot_name}
- Personality: ${personality} // professional, friendly, casual
- Industry: ${industry}

CAPABILITIES:
- Answer questions using the provided knowledge base
- Be helpful, accurate, and ${personality}
- Admit when you don't know something
- Suggest relevant documentation links when available
- Escalate to human agents when appropriate

GUIDELINES:
1. Always be polite and respectful
2. Keep responses concise (2-3 paragraphs max)
3. Use formatting for clarity (bullet points, numbered lists)
4. Include relevant links from knowledge base
5. If you're uncertain (confidence < 70%), acknowledge it and offer to escalate
6. Never make up information or provide incorrect details
7. Maintain conversation context across messages
8. Use customer's name if provided

ESCALATION TRIGGERS:
- Customer explicitly asks for human agent
- You detect frustration or anger
- Question is outside your knowledge base
- Billing, refund, or account deletion requests
- Complex technical issues
- Legal or compliance questions

RESPONSE FORMAT:
- Start with direct answer
- Provide additional context if helpful
- End with a follow-up question or next step
- Include links in markdown format: [Link Text](url)

${custom_instructions || ''}
`;
```

**User Prompt Template:**
```typescript
const userPrompt = `
KNOWLEDGE BASE CONTEXT:
${retrievedChunks.map((chunk, i) => `
[Source ${i + 1}]: ${chunk.metadata.title || chunk.metadata.filename}
${chunk.content}
${chunk.metadata.url ? `URL: ${chunk.metadata.url}` : ''}
`).join('\n---\n')}

CONVERSATION HISTORY:
${conversationHistory.map(msg => `
${msg.sender_type === 'customer' ? 'Customer' : msg.sender_name}: ${msg.content}
`).join('\n')}

CUSTOMER INFORMATION:
- Name: ${customer.name || 'Unknown'}
- Email: ${customer.email || 'Not provided'}
- Previous conversations: ${customer.previous_conversation_count}
${customer.metadata ? `- Additional info: ${JSON.stringify(customer.metadata)}` : ''}

CURRENT PAGE CONTEXT:
- URL: ${context.page_url}
- Referrer: ${context.referrer}
- Time on page: ${context.time_on_page_seconds}s

CUSTOMER'S QUESTION:
${currentMessage}

Please provide a helpful response based on the knowledge base context. If the information isn't available in the knowledge base, clearly state that and offer to connect them with a team member.
`;
```

**Response Generation Flow:**
```typescript
async function generateAIResponse(
  message: string,
  conversation: Conversation,
  chatbot: Chatbot
): Promise<AIResponse> {
  
  // 1. Detect intent and extract keywords
  const intent = await detectIntent(message);
  const keywords = extractKeywords(message);
  
  // 2. Retrieve relevant knowledge
  const embedding = await generateEmbedding(message);
  const relevantChunks = await vectorSearch(
    embedding,
    chatbot.id,
    topK: 5,
    minScore: 0.6
  );
  
  // 3. Check if escalation needed before generating response
  if (shouldEscalateBeforeResponse(message, intent, relevantChunks)) {
    return {
      type: 'escalation',
      reason: 'low_confidence',
      message: "I'd like to connect you with a team member who can better assist with this."
    };
  }
  
  // 4. Build context window
  const conversationHistory = await getRecentMessages(
    conversation.id,
    limit: 10
  );
  
  const systemPrompt = buildSystemPrompt(chatbot);
  const userPrompt = buildUserPrompt(
    message,
    relevantChunks,
    conversationHistory,
    conversation.customer,
    conversation.context
  );
  
  // 5. Select appropriate model
  const model = selectModel(message, conversation);
  
  // 6. Generate response with streaming
  const startTime = Date.now();
  let response = '';
  let sources: Source[] = [];
  
  const stream = await callLLMAPI(model, {
    system: systemPrompt,
    messages: [
      ...conversationHistory.map(msg => ({
        role: msg.sender_type === 'customer' ? 'user' : 'assistant',
        content: msg.content
      })),
      { role: 'user', content: userPrompt }
    ],
    temperature: chatbot.ai_settings.temperature,
    max_tokens: chatbot.ai_settings.max_tokens,
    stream: true
  });
  
  // 7. Stream response to frontend
  for await (const chunk of stream) {
    response += chunk.content;
    emitToWebSocket(conversation.id, 'message:streaming', {
      chunk: chunk.content
    });
  }
  
  // 8. Extract sources mentioned in response
  sources = extractSourcesFromChunks(relevantChunks, response);
  
  // 9. Calculate confidence score
  const confidenceScore = calculateConfidence(
    relevantChunks,
    response,
    intent
  );
  
  const processingTime = Date.now() - startTime;
  
  // 10. Check if escalation needed after response
  if (shouldEscalateAfterResponse(confidenceScore, conversation)) {
    await suggestEscalation(conversation.id);
  }
  
  // 11. Return response
  return {
    type: 'message',
    content: response,
    confidence_score: confidenceScore,
    sources: sources,
    model: model,
    processing_time_ms: processingTime,
    tokens_used: {
      input: countTokens(systemPrompt + userPrompt),
      output: countTokens(response)
    }
  };
}
```

---

#### **5.5.3 Confidence Scoring**

**Confidence Calculation:**
```typescript
function calculateConfidence(
  retrievedChunks: Chunk[],
  generatedResponse: string,
  intent: Intent
): number {
  let confidence = 0;
  
  // 1. Vector similarity score (40%)
  const avgSimilarity = retrievedChunks.reduce(
    (sum, chunk) => sum + chunk.score, 0
  ) / retrievedChunks.length;
  confidence += avgSimilarity * 0.4;
  
  // 2. Number of relevant chunks (20%)
  const highQualityChunks = retrievedChunks.filter(
    chunk => chunk.score > 0.75
  ).length;
  const chunkScore = Math.min(highQualityChunks / 3, 1);
  confidence += chunkScore * 0.2;
  
  // 3. Intent clarity (20%)
  const intentConfidence = intent.confidence;
  confidence += intentConfidence * 0.2;
  
  // 4. Response coherence (20%)
  const coherenceScore = analyzeResponseCoherence(
    generatedResponse,
    retrievedChunks
  );
  confidence += coherenceScore * 0.2;
  
  // Apply penalties
  if (generatedResponse.includes("I don't know") || 
      generatedResponse.includes("I'm not sure")) {
    confidence *= 0.7;
  }
  
  if (retrievedChunks.length === 0) {
    confidence = 0;
  }
  
  return Math.min(Math.max(confidence, 0), 1);
}
```

**Confidence Thresholds:**
- **> 0.85**: High confidence - respond directly
- **0.70 - 0.85**: Medium confidence - respond with caveat
- **0.50 - 0.70**: Low confidence - suggest escalation
- **< 0.50**: Very low confidence - escalate immediately

---

#### **5.5.4 Intent Detection**

**Predefined Intents:**
```typescript
enum Intent {
  // Account & Authentication
  PASSWORD_RESET = 'password_reset',
  ACCOUNT_CREATION = 'account_creation',
  LOGIN_ISSUE = 'login_issue',
  ACCOUNT_DELETION = 'account_deletion',
  
  // Billing & Subscription
  BILLING_QUESTION = 'billing_question',
  SUBSCRIPTION_UPGRADE = 'subscription_upgrade',
  SUBSCRIPTION_CANCEL = 'subscription_cancel',
  REFUND_REQUEST = 'refund_request',
  PAYMENT_ISSUE = 'payment_issue',
  
  // Technical Support
  TECHNICAL_ISSUE = 'technical_issue',
  FEATURE_QUESTION = 'feature_question',
  INTEGRATION_HELP = 'integration_help',
  BUG_REPORT = 'bug_report',
  
  // Product Information
  PRICING_INQUIRY = 'pricing_inquiry',
  FEATURE_REQUEST = 'feature_request',
  PRODUCT_COMPARISON = 'product_comparison',
  
  // General
  GENERAL_QUESTION = 'general_question',
  GREETING = 'greeting',
  GRATITUDE = 'gratitude',
  COMPLAINT = 'complaint',
  SPEAK_TO_HUMAN = 'speak_to_human',
  
  UNKNOWN = 'unknown'
}

// Intent detection using classification
async function detectIntent(message: string): Promise<{
  intent: Intent;
  confidence: number;
  entities: Entity[];
}> {
  // Use OpenAI function calling for intent classification
  const response = await openai.chat.completions.create({
    model: 'gpt-4o-mini',
    messages: [
      {
        role: 'system',
        content: 'You are an intent classifier for customer support messages.'
      },
      {
        role: 'user',
        content: message
      }
    ],
    functions: [
      {
        name: 'classify_intent',
        description: 'Classify the customer message intent',
        parameters: {
          type: 'object',
          properties: {
            intent: {
              type: 'string',
              enum: Object.values(Intent)
            },
            confidence: {
              type: 'number',
              description: 'Confidence score 0-1'
            },
            entities: {
              type: 'array',
              items: {
                type: 'object',
                properties: {
                  type: { type: 'string' },
                  value: { type: 'string' }
                }
              }
            }
          }
        }
      }
    ],
    function_call: { name: 'classify_intent' }
  });
  
  return JSON.parse(
    response.choices[0].message.function_call.arguments
  );
}
```

---

#### **5.5.5 Sentiment Analysis**

**Sentiment Detection:**
```typescript
enum Sentiment {
  VERY_NEGATIVE = 'very_negative',  // Angry, frustrated
  NEGATIVE = 'negative',             // Disappointed, unhappy
  NEUTRAL = 'neutral',               // Factual, informational
  POSITIVE = 'positive',             // Satisfied, appreciative
  VERY_POSITIVE = 'very_positive'    // Delighted, enthusiastic
}

async function analyzeSentiment(message: string): Promise<{
  sentiment: Sentiment;
  score: number; // -1 to 1
  emotions: string[];
}> {
  // Use sentiment analysis API or local model
  const result = await sentimentAPI.analyze(message);
  
  return {
    sentiment: classifySentiment(result.score),
    score: result.score,
    emotions: result.emotions // ['frustrated', 'urgent', etc.]
  };
}

// Frustration detection
function isFrustrated(
  message: string,
  sentiment: Sentiment,
  conversationHistory: Message[]
): boolean {
  // Keyword detection
  const frustrationKeywords = [
    'frustrated', 'angry', 'upset', 'terrible', 'awful',
    'worst', 'useless', 'disappointed', 'never', 'always breaking'
  ];
  
  const hasKeyword = frustrationKeywords.some(
    keyword => message.toLowerCase().includes(keyword)
  );
  
  // Sentiment check
  const isNegative = [
    Sentiment.NEGATIVE,
    Sentiment.VERY_NEGATIVE
  ].includes(sentiment);
  
  // Repetition check
  const recentMessages = conversationHistory.slice(-5);
  const hasRepeatedQuestion = recentMessages.some(
    msg => similarity(msg.content, message) > 0.8
  );
  
  // Escalation language
  const escalationPhrases = [
    'speak to manager', 'talk to human', 'this isn\'t working',
    'not helping', 'need real person'
  ];
  
  const hasEscalationLanguage = escalationPhrases.some(
    phrase => message.toLowerCase().includes(phrase)
  );
  
  return hasKeyword || 
         (isNegative && hasRepeatedQuestion) || 
         hasEscalationLanguage;
}
```

---

### **5.6 Analytics & Reporting**

#### **5.6.1 Real-time Metrics Dashboard**

**Metrics to Track:**

**Conversation Metrics:**
```typescript
interface ConversationMetrics {
  // Volume
  total_conversations: number;
  new_conversations_today: number;
  active_conversations: number;
  
  // Resolution
  ai_resolved: number;
  ai_resolution_rate: number; // percentage
  human_escalated: number;
  escalation_rate: number;
  
  // Performance
  avg_response_time_seconds: number;
  avg_first_response_time_seconds: number;
  avg_conversation_length_messages: number;
  avg_resolution_time_minutes: number;
  
  // Quality
  avg_satisfaction_rating: number; // 1-5
  total_satisfaction_responses: number;
  satisfaction_response_rate: number;
  nps_score: number; // Net Promoter Score
  
  // Engagement
  returning_customers: number;
  returning_customer_rate: number;
  avg_messages_per_conversation: number;
}
```

**Agent Metrics:**
```typescript
interface AgentMetrics {
  agent_id: string;
  agent_name: string;
  
  // Activity
  conversations_handled: number;
  messages_sent: number;
  active_time_minutes: number;
  
  // Performance
  avg_response_time_seconds: number;
  avg_resolution_time_minutes: number;
  conversations_per_hour: number;
  
  // Quality
  avg_satisfaction_rating: number;
  positive_feedback_count: number;
  negative_feedback_count: number;
  
  // Status
  current_status: 'online' | 'away' | 'offline';
  active_conversations: number;
  max_concurrent_conversations: number;
}
```

**API Endpoint:**
```typescript
// Request
GET /api/v1/analytics/dashboard?period=7d&chatbot_id=chatbot_abc123

// Response
{
  "period": {
    "start": "2025-09-28T00:00:00Z",
    "end": "2025-10-05T23:59:59Z"
  },
  "conversation_metrics": {
    "total_conversations": 1547,
    "new_conversations_today": 87,
    "active_conversations": 12,
    "ai_resolved": 1236,
    "ai_resolution_rate": 79.9,
    "human_escalated": 311,
    "escalation_rate": 20.1,
    "avg_response_time_seconds": 1.8,
    "avg_first_response_time_seconds": 2.1,
    "avg_conversation_length_messages": 5.3,
    "avg_resolution_time_minutes": 4.2,
    "avg_satisfaction_rating": 4.6,
    "total_satisfaction_responses": 892,
    "satisfaction_response_rate": 57.7,
    "nps_score": 72,
    "returning_customers": 234,
    "returning_customer_rate": 15.1,
    "avg_messages_per_conversation": 5.3
  },
  "agent_metrics": [
    {
      "agent_id": "agent_xyz",
      "agent_name": "Sarah Johnson",
      "conversations_handled": 67,
      "messages_sent": 312,
      "active_time_minutes": 1240,
      "avg_response_time_seconds": 45,
      "avg_resolution_time_minutes": 8.5,
      "conversations_per_hour": 3.2,
      "avg_satisfaction_rating": 4.8,
      "positive_feedback_count": 52,
      "negative_feedback_count": 3,
      "current_status": "online",
      "active_conversations": 2,
      "max_concurrent_conversations": 5
    }
    // ... more agents
  ],
  "trends": {
    "daily": [
      {
        "date": "2025-09-28",
        "conversations": 198,
        "ai_resolution_rate": 78.3,
        "avg_satisfaction": 4.5
      }
      // ... 7 days
    ],
    "hourly": [
      {
        "hour": 0,
        "conversations": 8,
        "ai_resolution_rate": 85.0
      }
      // ... 24 hours
    ]
  },
  "top_intents": [
    {
      "intent": "billing_question",
      "count": 342,
      "percentage": 22.1,
      "avg_resolution_time": 3.2
    }
    // ... top 10
  ],
  "common_questions": [
    {
      "question": "How do I reset my password?",
      "count": 67,
      "ai_success_rate": 95.5
    }
    // ... top 20
  ]
}
```

---

#### **5.6.2 Custom Reports**

**Report Builder:**
```typescript
// Create custom report
POST /api/v1/analytics/reports
{
  "name": "Weekly Performance Report",
  "description": "Weekly summary of key metrics",
  "type": "scheduled", // adhoc, scheduled
  "schedule": {
    "frequency": "weekly",
    "day_of_week": "monday",
    "time": "09:00",
    "timezone": "America/New_York"
  },
  "metrics": [
    "total_conversations",
    "ai_resolution_rate",
    "avg_satisfaction_rating",
    "escalation_rate"
  ],
  "filters": {
    "chatbot_id": "chatbot_abc123",
    "date_range": "last_7_days"
  },
  "format": "pdf", // pdf, csv, json
  "recipients": [
    "owner@example.com",
    "manager@example.com"
  ],
  "include_charts": true,
  "include_recommendations": true
}

// Generate report on-demand
POST /api/v1/analytics/reports/:id/generate

// Download report
GET /api/v1/analytics/reports/:id/download
```

---

#### **5.6.3 Export Data**

**Export Conversations:**
```typescript
// Request
POST /api/v1/analytics/export/conversations
{
  "filters": {
    "date_range": {
      "start": "2025-10-01T00:00:00Z",
      "end": "2025-10-05T23:59:59Z"
    },
    "status": ["resolved"],
    "chatbot_id": "chatbot_abc123"
  },
  "format": "csv", // csv, json, xlsx
  "fields": [
    "id",
    "created_at",
    "customer_email",
    "status",
    "message_count",
    "resolution_time_minutes",
    "satisfaction_rating",
    "assigned_agent"
  ],
  "include_messages": true
}

// Response (async job)
{
  "export_id": "export_xyz123",
  "status": "processing",
  "estimated_completion": "2025-10-05T16:05:00Z"
}

// Check status
GET /api/v1/analytics/exports/:id

// Download when ready
GET /api/v1/analytics/exports/:id/download
```

---

## **6. Technical Architecture**

### **6.1 System Architecture Diagram**

```
┌─────────────────────────────────────────────────────────────────┐
│                         CLIENT LAYER                             │
├─────────────────────────────────────────────────────────────────┤
│  ┌─────────────────┐  ┌──────────────────┐  ┌─────────────────┐│
│  │  Admin Dashboard│  │   Chat Widget    │  │  Mobile App     ││
│  │   (React SPA)   │  │  (Embeddable)    │  │  (Future)       ││
│  └────────┬────────┘  └────────┬─────────┘  └────────┬────────┘│
└───────────┼────────────────────┼──────────────────────┼─────────┘
            │                    │                      │
            │                    │                      │
┌───────────┼────────────────────┼──────────────────────┼─────────┐
│           │         API GATEWAY (Kong/AWS API Gateway)│         │
│           │                    │                      │         │
│  ┌────────▼────────────────────▼──────────────────────▼──────┐ │
│  │         Load Balancer (NGINX / AWS ALB)                   │ │
│  └────────┬────────────────────┬──────────────────────┬──────┘ │
└───────────┼────────────────────┼──────────────────────┼─────────┘
            │                    │                      │
┌───────────┼────────────────────┼──────────────────────┼─────────┐
│           │          APPLICATION LAYER                │         │
├───────────┴────────────────────┴──────────────────────┴─────────┤
│  ┌────────────────┐  ┌────────────────┐  ┌──────────────────┐  │
│  │  Web API       │  │  WebSocket     │  │  Background      │  │
│  │  (Node.js/     │  │  Server        │  │  Workers         │  │
│  │   NestJS)      │  │  (Socket.io)   │  │  (Bull Queue)    │  │
│  └───────┬────────┘  └───────┬────────┘  └────────┬─────────┘  │
└──────────┼─────────────────────┼────────────────────┼───────────┘
           │                    │                    │
┌──────────┼────────────────────┼────────────────────┼───────────┐
│          │         SERVICE LAYER                   │           │
├──────────┴─────────────────────┴────────────────────┴───────────┤
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────────────┐  │
│  │Auth Service  │  │Chat Service  │  │ Knowledge Base Svc   │  │
│  └──────┬───────┘  └──────┬───────┘  └──────────┬───────────┘  │
│  ┌──────┴──────┐  ┌───────┴───────┐  ┌──────────┴───────────┐  │
│  │Analytics Svc│  │Billing Service│  │  Notification Svc    │  │
│  └─────────────┘  └───────────────┘  └──────────────────────┘  │
└───────────────────────────┬──────────────────────────────────────┘
                           │
┌──────────────────────────┼──────────────────────────────────────┐
│          DATA LAYER      │                                      │
├──────────────────────────┴──────────────────────────────────────┤
│  ┌─────────────────┐  ┌──────────────┐  ┌──────────────────┐   │
│  │  PostgreSQL     │  │   Redis      │  │  Vector DB       │   │
│  │  (Primary DB)   │  │  (Cache/     │  │  (Pinecone/      │   │
│  │                 │  │   Sessions)  │  │   Weaviate)      │   │
│  └─────────────────┘  └──────────────┘  └──────────────────┘   │
│  ┌─────────────────┐  ┌──────────────┐  ┌──────────────────┐   │
│  │  S3/CloudFront  │  │ Elasticsearch│  │  ClickHouse      │   │
│  │  (File Storage) │  │  (Search)    │  │  (Analytics DB)  │   │
│  └─────────────────┘  └──────────────┘  └──────────────────┘   │
└──────────────────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────────────────┐
│                    EXTERNAL SERVICES                              │
├──────────────────────────────────────────────────────────────────┤
│  ┌─────────────┐  ┌─────────────┐  ┌──────────────┐             │
│  │OpenAI API   │  │Anthropic API│  │SendGrid      │             │
│  └─────────────┘  └─────────────┘  └──────────────┘             │
│  ┌─────────────┐  ┌─────────────┐  ┌──────────────┐             │
│  │Stripe       │  │Slack API    │  │Twilio        │             │
│  └─────────────┘  └─────────────┘  └──────────────┘             │
└──────────────────────────────────────────────────────────────────┘
```

---

### **6.2 Technology Stack**

#### **Frontend:**
```typescript
{
  "framework": "React 18.3",
  "language": "TypeScript 5.2",
  "build_tool": "Vite 5.0",
  "ui_library": "shadcn/ui",
  "styling": "Tailwind CSS 3.4",
  "state_management": "Zustand 4.5",
  "data_fetching": "@tanstack/react-query 5.0",
  "routing": "React Router 6.20",
  "forms": "React Hook Form 7.49",
  "charts": "Recharts 2.10",
  "websocket": "socket.io-client 4.7",
  "markdown": "react-markdown 9.0",
  "code_highlighting": "prism-react-renderer 2.3",
  "date_handling": "date-fns 3.0",
  "file_upload": "react-dropzone 14.2"
}
```

**Additional Frontend Libraries:**
- `react-window` - Virtual scrolling for performance
- `framer-motion` - Animations
- `jsPDF` - PDF generation
- `html2canvas` - Screenshot/export
- `emoji-mart` - Emoji picker
- `react-hot-toast` - Notifications

---

#### **Backend:**
```typescript
{
  "runtime": "Node.js 20 LTS",
  "framework": "NestJS 10.3",
  "language": "TypeScript 5.2",
  "orm": "Prisma 5.7",
  "validation": "class-validator 0.14",
  "websocket": "socket.io 4.7",
  "queue": "BullMQ 5.0",
  "caching": "ioredis 5.3",
  "authentication": "Passport.js + JWT",
  "file_processing": {
    "pdf": "pdf-parse 1.1.1",
    "docx": "mammoth 1.6.0",
    "scraping": "puppeteer 21.6.1"
  },
  "ai_sdks": {
    "openai": "openai 4.24.1",
    "anthropic": "@anthropic-ai/sdk 0.9.1"
  },
  "email": "@sendgrid/mail 8.1.0",
  "payment": "stripe 14.9.0",
  "monitoring": "@sentry/node 7.92.0"
}
```

---

#### **Infrastructure:**
```yaml
hosting:
  frontend: "Vercel / Netlify / AWS S3 + CloudFront"
  backend: "AWS ECS / Google Cloud Run / Railway"
  cdn: "CloudFlare / AWS CloudFront"

databases:
  primary: "PostgreSQL 16 (AWS RDS / Supabase)"
  cache: "Redis 7.2 (AWS ElastiCache / Upstash)"
  vector: "Pinecone / Weaviate / Qdrant"
  search: "Elasticsearch 8.11 (AWS OpenSearch)"
  analytics: "ClickHouse / BigQuery"

storage:
  files: "AWS S3 / Cloudflare R2"
  backups: "AWS S3 Glacier"

monitoring:
  apm: "Sentry / DataDog"
  logs: "AWS CloudWatch / Better Stack"
  metrics: "Prometheus + Grafana"
  uptime: "BetterUptime / Pingdom"

ci_cd:
  version_control: "GitHub"
  ci: "GitHub Actions"
  deployment: "Automated via Docker"
```

---

### **6.3 Scalability Architecture**

#### **Horizontal Scaling:**
```typescript
// Auto-scaling configuration
{
  "api_servers": {
    "min_instances": 2,
    "max_instances": 20,
    "target_cpu_utilization": 70,
    "target_memory_utilization": 80,
    "scale_up_threshold": "requests > 1000/min",
    "scale_down_cooldown": "5 minutes"
  },
  "websocket_servers": {
    "min_instances": 2,
    "max_instances": 10,
    "connections_per_instance": 10000,
    "sticky_sessions": true
  },
  "worker_nodes": {
    "document_processing": 5,
    "embedding_generation": 3,
    "email_sending": 2
  }
}
```

#### **Database Sharding Strategy:**
```typescript
// Shard by workspace_id for multi-tenancy
function getShardKey(workspace_id: string): string {
  const hash = md5(workspace_id);
  const shardNumber = parseInt(hash.substr(0, 8), 16) % NUM_SHARDS;
  return `shard_${shardNumber}`;
}

// Database connection pool
const connectionPools = {
  shard_0: createPool({ host: 'db-shard-0.example.com' }),
  shard_1: createPool({ host: 'db-shard-1.example.com' }),
  shard_2: createPool({ host: 'db-shard-2.example.com' })
};
```

#### **Caching Strategy:**
```typescript
// Multi-layer caching
1. Client-side: React Query cache (5 minutes)
2. CDN: Static assets (1 year), API responses (1 minute)
3. Application: Redis cache (15 minutes)
4. Database: Query result cache (5 minutes)

// Cache keys
'chatbot:{chatbot_id}:config' // 1 hour TTL
'conversation:{conversation_id}:messages' // 5 minutes TTL
'workspace:{workspace_id}:analytics:7d' // 10 minutes TTL
'knowledge_base:{chatbot_id}:chunks' // 1 day TTL
'user:{user_id}:permissions' // 30 minutes TTL

// Cache invalidation strategies
- Time-based: Automatic expiration
- Event-based: Invalidate on updates (pub/sub)
- Manual: Admin can clear specific caches
```

---

### **6.4 Security Architecture**

#### **Authentication & Authorization:**
```typescript
// JWT Token Structure
{
  "access_token": {
    "payload": {
      "user_id": "uuid",
      "workspace_id": "uuid",
      "role": "admin",
      "permissions": ["chatbot.create", "conversation.view"],
      "iat": 1696521600,
      "exp": 1696522500 // 15 minutes
    },
    "algorithm": "RS256", // Asymmetric encryption
    "issuer": "chatsupport.ai",
    "audience": "api.chatsupport.ai"
  },
  "refresh_token": {
    "stored_in": "httpOnly cookie",
    "expiry": "30 days",
    "rotation": "on each use"
  }
}

// API Key for widget authentication
{
  "api_key_format": "cs_live_[32_char_random]",
  "storage": "encrypted in database",
  "rate_limiting": "per API key",
  "scopes": ["widget.embed", "message.send"]
}
```

#### **Data Encryption:**
```yaml
in_transit:
  protocol: "TLS 1.3"
  certificate: "Let's Encrypt / AWS ACM"
  cipher_suites: "AES-256-GCM"
  
at_rest:
  database: "AES-256 encryption"
  file_storage: "S3 server-side encryption (SSE-S3)"
  backups: "Encrypted with KMS"
  secrets: "AWS Secrets Manager / HashiCorp Vault"

sensitive_fields:
  - customer_email: "AES-256 encrypted"
  - api_keys: "bcrypt hashed"
  - payment_info: "Stripe vault (PCI compliant)"
```

#### **Security Headers:**
```typescript
// Helmet.js configuration
{
  "Content-Security-Policy": "default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.chatsupport.ai",
  "X-Frame-Options": "DENY",
  "X-Content-Type-Options": "nosniff",
  "Strict-Transport-Security": "max-age=31536000; includeSubDomains",
  "X-XSS-Protection": "1; mode=block",
  "Referrer-Policy": "strict-origin-when-cross-origin",
  "Permissions-Policy": "geolocation=(), microphone=(), camera=()"
}
```

#### **Rate Limiting:**
```typescript
// Different limits for different endpoints
const rateLimits = {
  authentication: {
    login: '5 requests per 15 minutes per IP',
    signup: '3 requests per hour per IP',
    password_reset: '3 requests per hour per email'
  },
  api: {
    general: '1000 requests per hour per user',
    search: '100 requests per hour per user',
    export: '10 requests per day per workspace'
  },
  widget: {
    message_send: '10 messages per minute per visitor',
    conversation_start: '5 per hour per visitor'
  },
  webhook: {
    delivery: '100 per minute per workspace'
  }
};

// Implementation with Redis
async function checkRateLimit(
  key: string, 
  limit: number, 
  window: number
): Promise<boolean> {
  const current = await redis.incr(key);
  if (current === 1) {
    await redis.expire(key, window);
  }
  return current <= limit;
}
```

#### **Input Validation & Sanitization:**
```typescript
// Using class-validator
class CreateChatbotDto {
  @IsString()
  @MinLength(3)
  @MaxLength(50)
  @Matches(/^[a-zA-Z0-9\s]+$/)
  name: string;

  @IsEmail()
  @MaxLength(255)
  contact_email: string;

  @IsUrl()
  @IsOptional()
  website_url?: string;

  @IsHexColor()
  primary_color: string;

  @ValidateNested()
  @Type(() => AISettingsDto)
  ai_settings: AISettingsDto;
}

// Sanitize user content
function sanitizeUserInput(content: string): string {
  // Remove XSS attempts
  const sanitized = DOMPurify.sanitize(content, {
    ALLOWED_TAGS: ['b', 'i', 'em', 'strong', 'a', 'br'],
    ALLOWED_ATTR: ['href', 'target']
  });
  
  // Prevent SQL injection (use parameterized queries)
  // Prevent NoSQL injection (validate object structure)
  
  return sanitized;
}
```

---

### **6.5 Data Privacy & Compliance**

#### **GDPR Compliance:**
```typescript
// Right to access
GET /api/v1/privacy/data-export
Response: ZIP file with all user data

// Right to deletion
DELETE /api/v1/privacy/delete-account
- Anonymize conversations (keep for analytics)
- Delete personal information
- Remove from mailing lists
- Notify data processors

// Right to portability
GET /api/v1/privacy/data-portability
Format: JSON, CSV, or XML

// Data retention policy
{
  "conversations": "90 days after resolution",
  "analytics_aggregated": "indefinite",
  "customer_profiles": "until account deletion or 3 years inactive",
  "logs": "30 days",
  "backups": "30 days"
}

// Cookie consent
- Essential cookies only by default
- Analytics cookies: opt-in
- Marketing cookies: opt-in
- Cookie banner implementation
```

#### **Data Processing Agreement:**
```yaml
data_categories:
  - customer_contact_info: "name, email, phone"
  - conversation_data: "messages, timestamps, metadata"
  - usage_data: "page views, clicks, session duration"
  - technical_data: "IP address, user agent, device type"

data_processors:
  - OpenAI: "message processing"
  - Anthropic: "message processing"
  - SendGrid: "email delivery"
  - Stripe: "payment processing"
  - AWS: "hosting and storage"

security_measures:
  - encryption_at_rest: true
  - encryption_in_transit: true
  - access_controls: "role-based"
  - audit_logging: true
  - regular_security_audits: "quarterly"
  - penetration_testing: "annually"
```

---

## **7. System Design**

### **7.1 Database Schema**

#### **7.1.1 PostgreSQL Schema (Primary Database)**

```sql
-- ============================================
-- WORKSPACES & USERS
-- ============================================

CREATE TABLE workspaces (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  name VARCHAR(100) NOT NULL,
  slug VARCHAR(50) UNIQUE NOT NULL,
  logo_url TEXT,
  website_url TEXT,
  industry VARCHAR(50),
  company_size VARCHAR(20),
  
  -- Subscription
  subscription_plan VARCHAR(20) NOT NULL DEFAULT 'starter',
    -- starter, professional, enterprise
  subscription_status VARCHAR(20) NOT NULL DEFAULT 'active',
    -- active, cancelled, past_due, trialing
  trial_ends_at TIMESTAMP,
  current_period_start TIMESTAMP,
  current_period_end TIMESTAMP,
  
  -- Billing
  stripe_customer_id VARCHAR(100),
  stripe_subscription_id VARCHAR(100),
  
  -- Settings
  settings JSONB DEFAULT '{}',
  
  -- Metadata
  created_at TIMESTAMP NOT NULL DEFAULT NOW(),
  updated_at TIMESTAMP NOT NULL DEFAULT NOW(),
  deleted_at TIMESTAMP
);

CREATE INDEX idx_workspaces_slug ON workspaces(slug);
CREATE INDEX idx_workspaces_stripe_customer ON workspaces(stripe_customer_id);

-- ============================================

CREATE TABLE users (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  workspace_id UUID NOT NULL REFERENCES workspaces(id) ON DELETE CASCADE,
  
  -- Authentication
  email VARCHAR(255) UNIQUE NOT NULL,
  password_hash VARCHAR(255), -- NULL for OAuth users
  email_verified BOOLEAN DEFAULT FALSE,
  email_verification_token VARCHAR(100),
  
  -- Profile
  first_name VARCHAR(50),
  last_name VARCHAR(50),
  avatar_url TEXT,
  phone VARCHAR(20),
  timezone VARCHAR(50) DEFAULT 'UTC',
  
  -- Authorization
  role VARCHAR(20) NOT NULL DEFAULT 'agent',
    -- owner, admin, agent, viewer
  permissions JSONB DEFAULT '[]',
  
  -- OAuth
  google_id VARCHAR(100),
  oauth_provider VARCHAR(20), -- google, microsoft
  
  -- MFA
  mfa_enabled BOOLEAN DEFAULT FALSE,
  mfa_secret VARCHAR(100),
  
  -- Status
  status VARCHAR(20) NOT NULL DEFAULT 'active',
    -- active, inactive, suspended
  last_login_at TIMESTAMP,
  last_active_at TIMESTAMP,
  
  -- Metadata
  created_at TIMESTAMP NOT NULL DEFAULT NOW(),
  updated_at TIMESTAMP NOT NULL DEFAULT NOW(),
  deleted_at TIMESTAMP
);

CREATE INDEX idx_users_workspace ON users(workspace_id);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_google_id ON users(google_id);

-- ============================================
-- CHATBOTS
-- ============================================

CREATE TABLE chatbots (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  workspace_id UUID NOT NULL REFERENCES workspaces(id) ON DELETE CASCADE,
  
  -- Basic Info
  name VARCHAR(100) NOT NULL,
  description TEXT,
  status VARCHAR(20) NOT NULL DEFAULT 'active',
    -- active, inactive, archived
  
  -- Configuration
  personality VARCHAR(20) DEFAULT 'professional',
    -- professional, friendly, casual
  greeting_message TEXT NOT NULL DEFAULT 'Hi! How can I help you today?',
  offline_message TEXT,
  fallback_responses JSONB DEFAULT '[]',
  
  -- Business Hours
  business_hours JSONB,
  /*
  {
    "enabled": true,
    "timezone": "America/New_York",
    "schedule": {
      "monday": {"start": "09:00", "end": "17:00"},
      ...
    }
  }
  */
  
  -- AI Settings
  ai_model VARCHAR(50) DEFAULT 'gpt-4o-mini',
  ai_temperature DECIMAL(3,2) DEFAULT 0.70,
  ai_max_tokens INT DEFAULT 500,
  enable_escalation BOOLEAN DEFAULT TRUE,
  escalation_settings JSONB,
  /*
  {
    "low_confidence_threshold": 0.7,
    "frustration_detection": true,
    "explicit_request": true,
    "max_failed_responses": 3
  }
  */
  
  -- Widget Settings
  widget_settings JSONB,
  /*
  {
    "position": "bottom-right",
    "theme": {
      "primary_color": "#3B82F6",
      "secondary_color": "#1E40AF",
      ...
    },
    "avatar_url": "...",
    "button_text": "Chat with us",
    "size": "medium"
  }
  */
  
  -- Data Collection
  collect_email BOOLEAN DEFAULT TRUE,
  collect_name BOOLEAN DEFAULT TRUE,
  custom_fields JSONB DEFAULT '[]',
  
  -- Behavior
  response_delay_ms INT DEFAULT 1000,
  show_typing_indicator BOOLEAN DEFAULT TRUE,
  max_conversation_length INT DEFAULT 50,
  inactivity_timeout_ms INT DEFAULT 300000, -- 5 minutes
  
  -- API Key
  api_key VARCHAR(100) UNIQUE NOT NULL,
  
  -- Statistics (cached)
  total_conversations INT DEFAULT 0,
  total_messages INT DEFAULT 0,
  avg_satisfaction_rating DECIMAL(3,2),
  
  -- Metadata
  created_by UUID REFERENCES users(id),
  created_at TIMESTAMP NOT NULL DEFAULT NOW(),
  updated_at TIMESTAMP NOT NULL DEFAULT NOW(),
  deleted_at TIMESTAMP
);

CREATE INDEX idx_chatbots_workspace ON chatbots(workspace_id);
CREATE INDEX idx_chatbots_api_key ON chatbots(api_key);
CREATE INDEX idx_chatbots_status ON chatbots(status);

-- ============================================
-- KNOWLEDGE BASE
-- ============================================

CREATE TABLE knowledge_base_documents (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  chatbot_id UUID NOT NULL REFERENCES chatbots(id) ON DELETE CASCADE,
  
  -- File Info
  filename VARCHAR(255) NOT NULL,
  original_filename VARCHAR(255) NOT NULL,
  file_url TEXT NOT NULL,
  file_size_bytes BIGINT,
  mime_type VARCHAR(100),
  
  -- Processing
  status VARCHAR(20) NOT NULL DEFAULT 'pending',
    -- pending, processing, completed, failed
  processing_started_at TIMESTAMP,
  processing_completed_at TIMESTAMP,
  processing_error TEXT,
  
  -- Extraction
  extracted_text TEXT,
  text_length INT,
  page_count INT,
  
  -- Chunking
  chunk_count INT DEFAULT 0,
  chunk_strategy VARCHAR(50) DEFAULT 'semantic',
  
  -- Metadata
  metadata JSONB,
  /*
  {
    "title": "Getting Started Guide",
    "category": "documentation",
    "language": "en",
    "version": "1.0",
    "author": "..."
  }
  */
  
  -- Statistics
  usage_count INT DEFAULT 0,
  last_used_at TIMESTAMP,
  
  -- Audit
  uploaded_by UUID REFERENCES users(id),
  created_at TIMESTAMP NOT NULL DEFAULT NOW(),
  updated_at TIMESTAMP NOT NULL DEFAULT NOW(),
  deleted_at TIMESTAMP
);

CREATE INDEX idx_kb_docs_chatbot ON knowledge_base_documents(chatbot_id);
CREATE INDEX idx_kb_docs_status ON knowledge_base_documents(status);

-- ============================================

CREATE TABLE knowledge_base_urls (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  chatbot_id UUID NOT NULL REFERENCES chatbots(id) ON DELETE CASCADE,
  
  -- URL Info
  url TEXT NOT NULL,
  domain VARCHAR(255),
  
  -- Crawl Settings
  max_pages INT DEFAULT 50,
  max_depth INT DEFAULT 3,
  include_patterns JSONB DEFAULT '[]',
  exclude_patterns JSONB DEFAULT '[]',
  follow_external_links BOOLEAN DEFAULT FALSE,
  
  -- Crawl Status
  status VARCHAR(20) NOT NULL DEFAULT 'pending',
    -- pending, crawling, completed, failed
  crawl_job_id VARCHAR(100),
  pages_found INT DEFAULT 0,
  pages_processed INT DEFAULT 0,
  pages_failed INT DEFAULT 0,
  last_crawl_at TIMESTAMP,
  next_crawl_at TIMESTAMP,
  
  -- Schedule
  schedule_enabled BOOLEAN DEFAULT FALSE,
  schedule_frequency VARCHAR(20), -- daily, weekly, monthly
  schedule_config JSONB,
  
  -- Sitemap
  sitemap JSONB, -- Array of crawled URLs
  
  -- Metadata
  created_by UUID REFERENCES users(id),
  created_at TIMESTAMP NOT NULL DEFAULT NOW(),
  updated_at TIMESTAMP NOT NULL DEFAULT NOW()
);

CREATE INDEX idx_kb_urls_chatbot ON knowledge_base_urls(chatbot_id);
CREATE INDEX idx_kb_urls_status ON knowledge_base_urls(status);

-- ============================================

CREATE TABLE knowledge_base_faqs (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  chatbot_id UUID NOT NULL REFERENCES chatbots(id) ON DELETE CASCADE,
  
  -- Content
  question TEXT NOT NULL,
  answer TEXT NOT NULL,
  
  -- Organization
  category VARCHAR(50),
  tags JSONB DEFAULT '[]',
  priority VARCHAR(20) DEFAULT 'medium', -- high, medium, low
  
  -- Status
  status VARCHAR(20) NOT NULL DEFAULT 'active',
    -- active, inactive, draft
  
  -- Metadata
  metadata JSONB,
  
  -- Statistics
  usage_count INT DEFAULT 0,
  helpful_count INT DEFAULT 0,
  not_helpful_count INT DEFAULT 0,
  last_used_at TIMESTAMP,
  
  -- Embedding (for quick access)
  embedding_generated BOOLEAN DEFAULT FALSE,
  
  -- Audit
  created_by UUID REFERENCES users(id),
  created_at TIMESTAMP NOT NULL DEFAULT NOW(),
  updated_at TIMESTAMP NOT NULL DEFAULT NOW(),
  deleted_at TIMESTAMP
);

CREATE INDEX idx_kb_faqs_chatbot ON knowledge_base_faqs(chatbot_id);
CREATE INDEX idx_kb_faqs_status ON knowledge_base_faqs(status);
CREATE INDEX idx_kb_faqs_category ON knowledge_base_faqs(category);

-- ============================================
-- CONVERSATIONS & MESSAGES
-- ============================================

CREATE TABLE customers (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  workspace_id UUID NOT NULL REFERENCES workspaces(id) ON DELETE CASCADE,
  
  -- Identity
  email VARCHAR(255),
  name VARCHAR(100),
  phone VARCHAR(20),
  
  -- Profile
  avatar_url TEXT,
  company VARCHAR(100),
  title VARCHAR(100),
  
  -- Tracking
  visitor_id VARCHAR(100), -- Anonymous tracking before identification
  first_seen_at TIMESTAMP NOT NULL DEFAULT NOW(),
  last_seen_at TIMESTAMP,
  
  -- Metadata
  metadata JSONB DEFAULT '{}',
  custom_fields JSONB DEFAULT '{}',
  
  -- Statistics
  total_conversations INT DEFAULT 0,
  total_messages_sent INT DEFAULT 0,
  avg_satisfaction_rating DECIMAL(3,2),
  
  -- Timestamps
  created_at TIMESTAMP NOT NULL DEFAULT NOW(),
  updated_at TIMESTAMP NOT NULL DEFAULT NOW()
);

CREATE INDEX idx_customers_workspace ON customers(workspace_id);
CREATE INDEX idx_customers_email ON customers(email);
CREATE INDEX idx_customers_visitor ON customers(visitor_id);

-- ============================================

CREATE TABLE conversations (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  chatbot_id UUID NOT NULL REFERENCES chatbots(id) ON DELETE CASCADE,
  customer_id UUID REFERENCES customers(id) ON DELETE SET NULL,
  
  -- Status
  status VARCHAR(20) NOT NULL DEFAULT 'active',
    -- active, waiting, escalated, agent_handling, resolved, abandoned, archived
  
  -- Assignment
  assigned_agent_id UUID REFERENCES users(id) ON DELETE SET NULL,
  assigned_at TIMESTAMP,
  
  -- Context
  context JSONB,
  /*
  {
    "page_url": "https://example.com/pricing",
    "referrer": "https://google.com",
    "user_agent": "...",
    "device": "desktop",
    "browser": "Chrome 118",
    "location": {
      "country": "US",
      "city": "San Francisco"
    }
  }
  */
  
  -- Tags
  tags JSONB DEFAULT '[]',
  
  -- Metrics
  message_count INT DEFAULT 0,
  ai_message_count INT DEFAULT 0,
  agent_message_count INT DEFAULT 0,
  customer_message_count INT DEFAULT 0,
  
  -- Timing
  first_response_time_seconds INT,
  avg_response_time_seconds INT,
  total_duration_seconds INT,
  
  -- Satisfaction
  satisfaction_rating INT CHECK (satisfaction_rating BETWEEN 1 AND 5),
  satisfaction_comment TEXT,
  satisfaction_rated_at TIMESTAMP,
  
  -- Resolution
  resolved_by UUID REFERENCES users(id),
  resolved_at TIMESTAMP,
  resolution_note TEXT,
  
  -- Timestamps
  created_at TIMESTAMP NOT NULL DEFAULT NOW(),
  updated_at TIMESTAMP NOT NULL DEFAULT NOW(),
  last_message_at TIMESTAMP,
  archived_at TIMESTAMP
);

CREATE INDEX idx_conversations_chatbot ON conversations(chatbot_id);
CREATE INDEX idx_conversations_customer ON conversations(customer_id);
CREATE INDEX idx_conversations_status ON conversations(status);
CREATE INDEX idx_conversations_assigned_agent ON conversations(assigned_agent_id);
CREATE INDEX idx_conversations_created_at ON conversations(created_at DESC);
CREATE INDEX idx_conversations_tags ON conversations USING GIN(tags);

-- ============================================

CREATE TABLE messages (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  conversation_id UUID NOT NULL REFERENCES conversations(id) ON DELETE CASCADE,
  
  -- Sender
  sender_type VARCHAR(20) NOT NULL,
    -- customer, bot, agent, system
  sender_id UUID, -- customer_id, user_id (agent), or chatbot_id
  sender_name VARCHAR(100),
  
  -- Content
  content TEXT NOT NULL,
  content_type VARCHAR(20) DEFAULT 'text',
    -- text, image, file, card, button_group
  
  -- Attachments
  attachments JSONB DEFAULT '[]',
  /*
  [
    {
      "type": "image",
      "url": "https://...",
      "filename": "screenshot.png",
      "size_bytes": 524288
    }
  ]
  */
  
  -- AI Metadata (for bot messages)
  ai_metadata JSONB,
  /*
  {
    "model": "gpt-4o-mini",
    "confidence_score": 0.89,
    "processing_time_ms": 1847,
    "tokens_used": {"input": 450, "output": 120},
    "sources": [
      {
        "type": "faq",
        "id": "faq_xyz",
        "title": "...",
        "url": "..."
      }
    ]
  }
  */
  
  -- Intent (for customer messages)
  intent VARCHAR(50),
  intent_confidence DECIMAL(3,2),
  
  -- Sentiment
  sentiment VARCHAR(20),
  sentiment_score DECIMAL(3,2),
  
  -- Status
  is_read BOOLEAN DEFAULT FALSE,
  read_at TIMESTAMP,
  
  -- Timestamps
  created_at TIMESTAMP NOT NULL DEFAULT NOW(),
  edited_at TIMESTAMP,
  deleted_at TIMESTAMP
);

CREATE INDEX idx_messages_conversation ON messages(conversation_id);
CREATE INDEX idx_messages_sender ON messages(sender_type, sender_id);
CREATE INDEX idx_messages_created_at ON messages(created_at);

-- ============================================

CREATE TABLE conversation_events (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  conversation_id UUID NOT NULL REFERENCES conversations(id) ON DELETE CASCADE,
  
  -- Event
  event_type VARCHAR(50) NOT NULL,
    -- created, escalated, agent_joined, agent_left, returned_to_ai,
    -- resolved, reopened, tagged, status_changed
  event_data JSONB,
  
  -- Actor
  triggered_by_type VARCHAR(20), -- user, system, automation
  triggered_by_id UUID,
  
  -- Timestamp
  created_at TIMESTAMP NOT NULL DEFAULT NOW()
);

CREATE INDEX idx_conversation_events_conversation ON conversation_events(conversation_id);
CREATE INDEX idx_conversation_events_type ON conversation_events(event_type);

-- ============================================

CREATE TABLE internal_notes (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  conversation_id UUID NOT NULL REFERENCES conversations(id) ON DELETE CASCADE,
  
  -- Content
  content TEXT NOT NULL,
  
  -- Mentions
  mentioned_users JSONB DEFAULT '[]', -- Array of user IDs
  
  -- Author
  author_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  
  -- Timestamps
  created_at TIMESTAMP NOT NULL DEFAULT NOW(),
  updated_at TIMESTAMP NOT NULL DEFAULT NOW(),
  deleted_at TIMESTAMP
);

CREATE INDEX idx_internal_notes_conversation ON internal_notes(conversation_id);
CREATE INDEX idx_internal_notes_author ON internal_notes(author_id);

-- ============================================
-- ANALYTICS
-- ============================================

CREATE TABLE conversation_analytics (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  conversation_id UUID NOT NULL REFERENCES conversations(id) ON DELETE CASCADE,
  chatbot_id UUID NOT NULL REFERENCES chatbots(id) ON DELETE CASCADE,
  workspace_id UUID NOT NULL REFERENCES workspaces(id) ON DELETE CASCADE,
  
  -- Date partitioning
  date DATE NOT NULL,
  hour INT NOT NULL CHECK (hour BETWEEN 0 AND 23),
  
  -- Metrics
  message_count INT NOT NULL DEFAULT 0,
  ai_resolved BOOLEAN,
  escalated BOOLEAN,
  satisfaction_rating INT,
  
  -- Timing
  first_response_time_seconds INT,
  total_duration_seconds INT,
  
  -- Classification
  primary_intent VARCHAR(50),
  primary_category VARCHAR(50),
  tags JSONB DEFAULT '[]',
  
  -- Context
  customer_returning BOOLEAN,
  device_type VARCHAR(20),
  
  -- Timestamps
  created_at TIMESTAMP NOT NULL DEFAULT NOW()
);

CREATE INDEX idx_conv_analytics_date ON conversation_analytics(date DESC);
CREATE INDEX idx_conv_analytics_chatbot_date ON conversation_analytics(chatbot_id, date DESC);
CREATE INDEX idx_conv_analytics_workspace_date ON conversation_analytics(workspace_id, date DESC);

-- Partition by month for better performance
CREATE TABLE conversation_analytics_2025_10 PARTITION OF conversation_analytics
  FOR VALUES FROM ('2025-10-01') TO ('2025-11-01');

-- ============================================
-- INTEGRATIONS
-- ============================================

CREATE TABLE integrations (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  workspace_id UUID NOT NULL REFERENCES workspaces(id) ON DELETE CASCADE,
  
  -- Integration Type
  provider VARCHAR(50) NOT NULL,
    -- slack, email, salesforce, hubspot, zapier, custom_webhook
  
  -- Configuration
  config JSONB NOT NULL,
  /*
  {
    "access_token": "encrypted_token",
    "refresh_token": "encrypted_token",
    "webhook_url": "https://...",
    "channel_id": "C1234567890",
    ...
  }
  */
  
  -- Status
  status VARCHAR(20) NOT NULL DEFAULT 'active',
    -- active, inactive, error, pending_auth
  last_error TEXT,
  last_sync_at TIMESTAMP,
  
  -- Settings
  settings JSONB,
  /*
  {
    "events": ["conversation.created", "conversation.resolved"],
    "filters": {...},
    "field_mapping": {...}
  }
  */
  
  -- Metadata
  created_by UUID REFERENCES users(id),
  created_at TIMESTAMP NOT NULL DEFAULT NOW(),
  updated_at TIMESTAMP NOT NULL DEFAULT NOW()
);

CREATE INDEX idx_integrations_workspace ON integrations(workspace_id);
CREATE INDEX idx_integrations_provider ON integrations(provider);

-- ============================================
-- BILLING
-- ============================================

CREATE TABLE invoices (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  workspace_id UUID NOT NULL REFERENCES workspaces(id) ON DELETE CASCADE,
  
  -- Stripe
  stripe_invoice_id VARCHAR(100) UNIQUE NOT NULL,
  stripe_payment_intent_id VARCHAR(100),
  
  -- Amount
  amount_cents INT NOT NULL,
  currency VARCHAR(3) DEFAULT 'USD',
  tax_cents INT DEFAULT 0,
  total_cents INT NOT NULL,
  
  -- Status
  status VARCHAR(20) NOT NULL,
    -- draft, open, paid, void, uncollectible
  
  -- Period
  period_start TIMESTAMP NOT NULL,
  period_end TIMESTAMP NOT NULL,
  
  -- Invoice
  invoice_pdf_url TEXT,
  invoice_number VARCHAR(50),
  
  -- Timestamps
  paid_at TIMESTAMP,
  created_at TIMESTAMP NOT NULL DEFAULT NOW()
);

CREATE INDEX idx_invoices_workspace ON invoices(workspace_id);
CREATE INDEX idx_invoices_stripe ON invoices(stripe_invoice_id);

-- ============================================

CREATE TABLE usage_records (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  workspace_id UUID NOT NULL REFERENCES workspaces(id) ON DELETE CASCADE,
  
  -- Metric
  metric VARCHAR(50) NOT NULL,
    -- conversations, messages, ai_tokens, storage_mb, team_members
  value INT NOT NULL,
  
  -- Period
  date DATE NOT NULL,
  
  -- Metadata
  metadata JSONB,
  
  -- Timestamp
  created_at TIMESTAMP NOT NULL DEFAULT NOW()
);

CREATE INDEX idx_usage_records_workspace_date ON usage_records(workspace_id, date DESC);
CREATE INDEX idx_usage_records_metric ON usage_records(metric);

-- ============================================
-- AUDIT LOGS
-- ============================================

CREATE TABLE audit_logs (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  workspace_id UUID NOT NULL REFERENCES workspaces(id) ON DELETE CASCADE,
  
  -- Actor
  user_id UUID REFERENCES users(id),
  user_email VARCHAR(255),
  ip_address INET,
  user_agent TEXT,
  
  -- Action
  action VARCHAR(100) NOT NULL,
    -- user.login, chatbot.created, conversation.escalated, etc.
  resource_type VARCHAR(50),
  resource_id UUID,
  
  -- Details
  changes JSONB,
  /*
  {
    "before": {...},
    "after": {...}
  }
  */
  
  -- Context
  metadata JSONB,
  
  -- Timestamp
  created_at TIMESTAMP NOT NULL DEFAULT NOW()
);

CREATE INDEX idx_audit_logs_workspace ON audit_logs(workspace_id);
CREATE INDEX idx_audit_logs_user ON audit_logs(user_id);
CREATE INDEX idx_audit_logs_action ON audit_logs(action);
CREATE INDEX idx_audit_logs_created_at ON audit_logs(created_at DESC);
```

---

#### **7.1.2 Vector Database Schema (Pinecone/Weaviate)**

```typescript
// Pinecone Index Configuration
{
  "index_name": "chatsupport-knowledge-base",
  "dimension": 1536, // text-embedding-3-small
  "metric": "cosine",
  "pod_type": "p1.x1", // or p2.x1 for production
  "replicas": 2,
  "shards": 1,
  
  // Namespace per chatbot for isolation
  "namespace_format": "chatbot_{chatbot_id}",
  
  // Vector metadata
  "metadata_config": {
    "indexed": [
      "chatbot_id",
      "source_type", // document, url, faq
      "source_id",
      "category",
      "language",
      "created_at"
    ]
  }
}

// Vector Record Structure
{
  "id": "chunk_{uuid}",
  "values": [0.012, -0.034, ...], // 1536 dimensions
  "metadata": {
    "chatbot_id": "chatbot_abc123",
    "source_type": "document", // document, url, faq
    "source_id": "doc_xyz789",
    "content": "To reset your password, go to...",
    "content_length": 245,
    "chunk_index": 3,
    "total_chunks": 47,
    
    // Document-specific
    "filename": "getting-started.pdf",
    "page_number": 5,
    "section_title": "Account Management",
    
    // URL-specific
    "url": "https://docs.example.com/password-reset",
    "page_title": "Password Reset Guide",
    
    // FAQ-specific
    "question": "How do I reset my password?",
    "category": "account",
    "tags": ["password", "security"],
    
    //