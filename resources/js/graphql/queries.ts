import gql from 'graphql-tag';

export const GET_TEAMS = gql`
    query GetTeams {
        teams {
            id
            name
            description
            user_id
            owner {
                id
                name
                email
            }
            members {
                id
                name
                email
            }
            membersWithRoles {
                id
                name
                email
                role
            }
            projects {
                id
                title
                description
                diagrams {
                    id
                    title
                    description
                }
            }
        }
    }
`;

export const GET_PROJECTS = gql`
    query GetProjects($team_id: ID) {
        projects(team_id: $team_id) {
            id
            team_id
            title
            description
            sharedUsers {
                id
                name
                email
            }
            diagrams {
                id
                title
                description
            }
        }
    }
`;

export const GET_SHARED_PROJECTS = gql`
    query GetSharedProjects {
        sharedProjects {
            id
            title
            description
            team_id
            team {
                id
                name
            }
            diagrams {
                id
                title
                description
            }
            sharedUsers {
                id
                name
                email
            }
        }
    }
`;

export const GET_DIAGRAM = gql`
    query GetDiagram($id: ID!) {
        diagram(id: $id) {
            id
            project_id
            title
            description
            project {
                id
                title
                team_id
            }
            nodes {
                id
                diagram_id
                type
                label
                position_x
                position_y
                metadata
            }
            edges {
                id
                diagram_id
                source_node_id
                target_node_id
                source_handle
                target_handle
                label
                type
            }
        }
    }
`;

export const CREATE_TEAM = gql`
    mutation CreateTeam($name: String!, $description: String, $user_id: ID) {
        createTeam(name: $name, description: $description, user_id: $user_id) {
            id
            name
            description
        }
    }
`;

export const UPDATE_TEAM = gql`
    mutation UpdateTeam($id: ID!, $name: String, $description: String) {
        updateTeam(id: $id, name: $name, description: $description) {
            id
            name
            description
        }
    }
`;

export const DELETE_TEAM = gql`
    mutation DeleteTeam($id: ID!) {
        deleteTeam(id: $id) {
            id
        }
    }
`;

export const ADD_TEAM_MEMBER = gql`
    mutation AddTeamMember($team_id: ID!, $email: String!, $role: String) {
        addTeamMember(team_id: $team_id, email: $email, role: $role) {
            id
            name
            membersWithRoles {
                id
                name
                email
                role
            }
        }
    }
`;

export const REMOVE_TEAM_MEMBER = gql`
    mutation RemoveTeamMember($team_id: ID!, $user_id: ID!) {
        removeTeamMember(team_id: $team_id, user_id: $user_id) {
            id
            name
            membersWithRoles {
                id
                name
                email
                role
            }
        }
    }
`;

export const UPDATE_TEAM_MEMBER_ROLE = gql`
    mutation UpdateTeamMemberRole($team_id: ID!, $user_id: ID!, $role: String!) {
        updateTeamMemberRole(team_id: $team_id, user_id: $user_id, role: $role) {
            id
            name
            membersWithRoles {
                id
                name
                email
                role
            }
        }
    }
`;

export const CREATE_PROJECT = gql`
    mutation CreateProject($title: String!, $description: String, $team_id: ID) {
        createProject(title: $title, description: $description, team_id: $team_id) {
            id
            team_id
            title
            description
        }
    }
`;

export const SHARE_PROJECT = gql`
    mutation ShareProject($project_id: ID!, $email: String!) {
        shareProject(project_id: $project_id, email: $email) {
            id
            title
            sharedUsers {
                id
                name
                email
            }
        }
    }
`;

export const REVOKE_PROJECT_ACCESS = gql`
    mutation RevokeProjectAccess($project_id: ID!, $user_id: ID!) {
        revokeProjectAccess(project_id: $project_id, user_id: $user_id) {
            id
            title
            sharedUsers {
                id
                name
                email
            }
        }
    }
`;

export const CREATE_DIAGRAM = gql`
    mutation CreateDiagram($project_id: ID!, $title: String!, $description: String) {
        createDiagram(project_id: $project_id, title: $title, description: $description) {
            id
            project_id
            title
            description
        }
    }
`;

export const UPDATE_DIAGRAM = gql`
    mutation UpdateDiagram($id: ID!, $title: String, $description: String) {
        updateDiagram(id: $id, title: $title, description: $description) {
            id
            project_id
            title
            description
        }
    }
`;

export const CREATE_NODE = gql`
    mutation CreateNode(
        $diagram_id: ID!
        $type: String!
        $label: String!
        $position_x: Float!
        $position_y: Float!
        $metadata: JSON
    ) {
        createNode(
            diagram_id: $diagram_id
            type: $type
            label: $label
            position_x: $position_x
            position_y: $position_y
            metadata: $metadata
        ) {
            id
            diagram_id
            type
            label
            position_x
            position_y
            metadata
        }
    }
`;

export const UPDATE_NODE = gql`
    mutation UpdateNode(
        $id: ID!
        $label: String
        $type: String
        $metadata: JSON
    ) {
        updateNode(
            id: $id
            label: $label
            type: $type
            metadata: $metadata
        ) {
            id
            label
            type
            metadata
        }
    }
`;

export const UPDATE_NODE_POSITION = gql`
    mutation UpdateNodePosition($id: ID!, $position_x: Float!, $position_y: Float!) {
        updateNodePosition(id: $id, position_x: $position_x, position_y: $position_y) {
            id
            position_x
            position_y
        }
    }
`;

export const DELETE_NODE = gql`
    mutation DeleteNode($id: ID!) {
        deleteNode(id: $id) {
            id
        }
    }
`;

export const CREATE_EDGE = gql`
    mutation CreateEdge(
        $diagram_id: ID!
        $source_node_id: ID!
        $target_node_id: ID!
        $source_handle: String
        $target_handle: String
        $label: String
        $type: String
    ) {
        createEdge(
            diagram_id: $diagram_id
            source_node_id: $source_node_id
            target_node_id: $target_node_id
            source_handle: $source_handle
            target_handle: $target_handle
            label: $label
            type: $type
        ) {
            id
            diagram_id
            source_node_id
            target_node_id
            source_handle
            target_handle
            label
            type
        }
    }
`;

export const UPDATE_EDGE = gql`
    mutation UpdateEdge($id: ID!, $source_handle: String, $target_handle: String, $label: String, $type: String) {
        updateEdge(id: $id, source_handle: $source_handle, target_handle: $target_handle, label: $label, type: $type) {
            id
            source_handle
            target_handle
            label
            type
        }
    }
`;

export const DELETE_EDGE = gql`
    mutation DeleteEdge($id: ID!) {
        deleteEdge(id: $id) {
            id
        }
    }
`;
