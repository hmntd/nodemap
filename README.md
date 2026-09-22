# Nodemap - Microservices Architecture Diagramming Platform

Nodemap is a real-time collaborative web application designed for enterprise software architects and engineering teams to design, visualize, and manage microservices topology diagrams. Built with Laravel, Inertia.js, Vue 3, GraphQL, and Vue Flow, Nodemap provides multi-tenant workspaces, granular role-based access control, interactive drag-and-drop canvas editing, and multi-format schema exports.

---

## Technology Stack

### Backend
- **Framework**: Laravel 13 (PHP 8.3+)
- **API Engine**: Nuwave Lighthouse GraphQL API
- **Database**: PostgreSQL (managed via Laravel Sail / Docker)
- **Authentication & Authorization**: Laravel Inertia / Session Auth with 3-tier workspace role authorization

### Frontend
- **Framework**: Vue 3 (Composition API / `<script setup>`) with TypeScript
- **Routing & State**: Inertia.js (Vite integration), Apollo Client (`@vue/apollo-composable`)
- **Canvas Engine**: `@vue-flow/core`, `@vue-flow/background`, `@vue-flow/controls`, `@vue-flow/minimap`
- **Styling**: Tailwind CSS v4 (Custom pitch-black dark theme)
- **Image Processing**: `html-to-image` for client-side PNG and JPEG schema rendering
- **Iconography**: Lucide Vue (`@lucide/vue`)

---

## Key Features

### 1. Workspaces & 3-Tier Role Management
- **Workspaces (`Teams`)**: Workspaces encapsulate projects, diagrams, and members.
- **Per-User Workspace Uniqueness**: Workspace names are unique per user account (`<user_id, name>`), allowing identical workspace names across different user accounts.
- **3-Tier Hierarchy**:
  - **Creator**: Workspace owner with full system privileges, role assignment capabilities, and workspace management rights.
  - **Admin**: Can invite or remove members and manage workspace projects.
  - **User**: View-only access to workspace resources.
- **Management Portal**: Dedicated `/workspaces` portal page for viewing, creating, and managing workspaces and member roles.

### 2. Projects & External Access Sharing
- **Workspace Projects**: Projects group diagram boards within a workspace. Diagram titles are strictly validated for uniqueness per workspace.
- **Single-Project External Sharing**: Invite external users (via email) to collaborate on a single project without granting access to other projects within the host workspace.

### 3. Interactive Architecture Canvas
- **Custom Node Components**: Specialized node types designed for enterprise system topologies:
  - Microservice API
  - Database Cluster (SQL / NoSQL)
  - Message Queue / Broker (Kafka, RabbitMQ)
  - Client Application (Web, Mobile)
  - External API / Cloud Gateway
- **Single-Relation Enforcement**: Restricts connection topology to a single active edge between any two nodes. Creating a new connection automatically removes any pre-existing connection between the pair.
- **Multi-Direction Handles**: Four-position connection handles (Top, Bottom, Left, Right) with explicit handle ID routing (`source_handle` and `target_handle`) stored in the database.
- **Dynamic Node Metadata**: Inspect and update stack technology, port numbers, replica counts, and custom key-value metadata properties in real time.
- **Real-Time Collaboration**: Polled GraphQL synchronization ensuring canvas state updates across active browser sessions.

### 4. Verification Modals
- **Node Deletion Confirmation**: Styled modal verification requiring confirmation before deleting any architecture node and its connected edges.

### 5. Diagram & Code Export Capabilities
- **High-Resolution Image Export**: Export full schema diagrams in PNG or JPEG format with customizable background rendering and instant preview.
- **Code Export Generators**: Generate diagram representations in:
  - Mermaid.js
  - PlantUML
  - JSON Schema

### 6. Dark-Themed MiniMap & Controls
- **Custom Canvas MiniMap**: Styled MiniMap featuring a dark viewport mask (`rgba(0,0,0,0.85)`), crisp white frame border, and node type color indicators:
  - Microservice (Emerald Green)
  - Database (Indigo Blue)
  - Message Queue (Amber Orange)
  - Client App (Purple)
  - External API (Rose Red)

---

## Installation & Setup

### Prerequisites
- Docker & Docker Compose
- Node.js (v20+) and npm
- PHP 8.3+ and Composer

### Setup Steps

1. Clone the repository:
```bash
git clone https://github.com/your-username/nodemap.git
cd nodemap
```

2. Copy environment file:
```bash
cp .env.example .env
```

3. Install PHP and Node dependencies:
```bash
composer install
npm install
```

4. Start Docker Sail environment:
```bash
./vendor/bin/sail up -d
```

5. Generate application key and run migrations:
```bash
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate
```

6. Build frontend assets:
```bash
npm run build
```

7. Start development server:
```bash
npm run dev
```

The application will be accessible at `http://localhost`.

---

## Testing

Run the PHPUnit test suite inside the Sail container:
```bash
./vendor/bin/sail test
```

---

## License

The Nodemap platform is open-source software licensed under the MIT License.
