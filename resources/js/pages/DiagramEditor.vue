<script setup lang="ts">
import { ref, computed, watch, markRaw } from 'vue';
import { useQuery, useMutation } from '@vue/apollo-composable';
import { VueFlow, useVueFlow, type NodeTypesObject } from '@vue-flow/core';
import { Background } from '@vue-flow/background';
import { Controls } from '@vue-flow/controls';
import { MiniMap } from '@vue-flow/minimap';
import { router, usePage } from '@inertiajs/vue3';

import {
    GET_TEAMS,
    GET_PROJECTS,
    GET_SHARED_PROJECTS,
    GET_DIAGRAM,
    CREATE_TEAM,
    UPDATE_TEAM,
    ADD_TEAM_MEMBER,
    CREATE_PROJECT,
    SHARE_PROJECT,
    REVOKE_PROJECT_ACCESS,
    CREATE_DIAGRAM,
    UPDATE_DIAGRAM,
    CREATE_NODE,
    UPDATE_NODE,
    UPDATE_NODE_POSITION,
    DELETE_NODE,
    CREATE_EDGE,
    UPDATE_EDGE,
    DELETE_EDGE,
} from '@/graphql/queries';

import ArchitectureNode from '@/components/diagram/ArchitectureNode.vue';
import DiagramHeader from '@/components/diagram/DiagramHeader.vue';
import NodeInspector from '@/components/diagram/NodeInspector.vue';
import ExportModal from '@/components/diagram/ExportModal.vue';
import DeleteNodeModal from '@/components/diagram/DeleteNodeModal.vue';
import TeamModal from '@/components/diagram/TeamModal.vue';
import ProjectModal from '@/components/diagram/ProjectModal.vue';
import DiagramModal from '@/components/diagram/DiagramModal.vue';

import {
    Server,
    Database,
    Layers,
    Monitor,
    Globe,
    Plus,
    FolderPlus,
    Loader2,
    Sparkles,
    ChevronRight,
    Share2,
} from '@lucide/vue';

const nodeTypes = {
    service: markRaw(ArchitectureNode),
    database: markRaw(ArchitectureNode),
    queue: markRaw(ArchitectureNode),
    client: markRaw(ArchitectureNode),
    external_api: markRaw(ArchitectureNode),
} as unknown as NodeTypesObject;

const page = usePage();
const authUser = computed(() => page.props.auth?.user || null);

const activeTeamId = ref<string>('');
const activeDiagramId = ref<string>('');
const selectedElement = ref<{ type: 'node' | 'edge'; item: any } | null>(null);

// Modals
const showExportModal = ref(false);
const showNewTeamModal = ref(false);
const showEditTeamModal = ref(false);
const showTeamMembersModal = ref(false);
const showNewProjectModal = ref(false);
const showShareProjectModal = ref(false);
const showNewDiagramModal = ref(false);
const showEditDiagramModal = ref(false);
const showDeleteNodeModal = ref(false);

// Form Inputs & Validation Errors
const newTeamName = ref('');
const newTeamDesc = ref('');
const createTeamError = ref('');

const editTeamName = ref('');
const editTeamDesc = ref('');
const editTeamError = ref('');

const inviteMemberEmail = ref('');
const inviteMemberError = ref('');
const inviteMemberSuccess = ref('');

const newProjectTitle = ref('');
const newProjectDesc = ref('');
const createProjectError = ref('');

const selectedProjectToShare = ref<any>(null);
const shareEmail = ref('');
const shareProjectError = ref('');
const shareProjectSuccess = ref('');

const newDiagramTitle = ref('');
const newDiagramDesc = ref('');
const createDiagramError = ref('');

const editDiagramTitle = ref('');
const editDiagramDesc = ref('');
const editDiagramError = ref('');

// Sync Status
const isSaving = ref(false);
const saveMessage = ref('Live Sync Connected');

// Apollo GraphQL Queries
const { result: teamsResult, refetch: refetchTeams } = useQuery(GET_TEAMS);

const { result: projectsResult, refetch: refetchProjects } = useQuery(
    GET_PROJECTS,
    () => ({ team_id: activeTeamId.value || null }),
    { enabled: true },
);

const { result: sharedProjectsResult, refetch: refetchSharedProjects } =
    useQuery(GET_SHARED_PROJECTS);

// Real-Time Simultaneous Collaboration Polling (pollInterval: 1500ms)
const {
    result: diagramResult,
    loading: diagramLoading,
    refetch: refetchDiagram,
} = useQuery(GET_DIAGRAM, () => ({ id: activeDiagramId.value }), {
    enabled: computed(() => !!activeDiagramId.value),
    pollInterval: 1500,
});

// Apollo GraphQL Mutations
const { mutate: createTeamMut } = useMutation(CREATE_TEAM);
const { mutate: updateTeamMut } = useMutation(UPDATE_TEAM);
const { mutate: addTeamMemberMut } = useMutation(ADD_TEAM_MEMBER);
const { mutate: createProjectMut } = useMutation(CREATE_PROJECT);
const { mutate: shareProjectMut } = useMutation(SHARE_PROJECT);
const { mutate: revokeProjectAccessMut } = useMutation(REVOKE_PROJECT_ACCESS);
const { mutate: createDiagramMut } = useMutation(CREATE_DIAGRAM);
const { mutate: updateDiagramMut } = useMutation(UPDATE_DIAGRAM);
const { mutate: createNodeMut } = useMutation(CREATE_NODE);
const { mutate: updateNodeMut } = useMutation(UPDATE_NODE);
const { mutate: updateNodePositionMut } = useMutation(UPDATE_NODE_POSITION);
const { mutate: deleteNodeMut } = useMutation(DELETE_NODE);

const { mutate: createEdgeMut } = useMutation(CREATE_EDGE);
const { mutate: updateEdgeMut } = useMutation(UPDATE_EDGE);
const { mutate: deleteEdgeMut } = useMutation(DELETE_EDGE);

const teams = computed(() => teamsResult.value?.teams || []);
const projects = computed(() => projectsResult.value?.projects || []);
const sharedProjects = computed(
    () => sharedProjectsResult.value?.sharedProjects || [],
);
const currentDiagram = computed(() => diagramResult.value?.diagram || null);

const currentActiveTeam = computed(() => {
    return (
        teams.value.find((t: any) => String(t.id) === activeTeamId.value) ||
        null
    );
});

// Dropdown Options
const teamOptions = computed(() => {
    return teams.value.map((t: any) => {
        let roleBadge = 'User';
        if (String(t.user_id) === String(authUser.value?.id)) {
            roleBadge = 'Creator';
        } else {
            const member = (t.membersWithRoles || []).find(
                (m: any) => String(m.id) === String(authUser.value?.id),
            );
            if (member && member.role === 'admin') {
                roleBadge = 'Admin';
            }
        }
        return {
            value: String(t.id),
            label: t.name,
            description: t.description || undefined,
            badge: roleBadge,
        };
    });
});

const boardOptions = computed(() => {
    const options: any[] = [];
    projects.value.forEach((proj: any) => {
        (proj.diagrams || []).forEach((diag: any) => {
            options.push({
                value: String(diag.id),
                label: diag.title,
                description: proj.title ? `Project: ${proj.title}` : undefined,
                badge: proj.title ? 'Team Board' : 'Board',
            });
        });
    });
    sharedProjects.value.forEach((proj: any) => {
        (proj.diagrams || []).forEach((diag: any) => {
            options.push({
                value: String(diag.id),
                label: diag.title,
                description: `Shared from: ${proj.team?.name || 'External'}`,
                badge: 'Shared',
            });
        });
    });
    return options;
});

// Default Selection Watchers
watch(
    teams,
    (newTeams) => {
        if (!activeTeamId.value && newTeams.length > 0) {
            activeTeamId.value = String(newTeams[0].id);
        }
    },
    { immediate: true },
);

watch(
    projects,
    (newProjects) => {
        if (newProjects.length > 0) {
            const firstDiagram = newProjects[0]?.diagrams?.[0];
            if (firstDiagram && !activeDiagramId.value) {
                activeDiagramId.value = String(firstDiagram.id);
            }
        }
    },
    { immediate: true },
);

// VueFlow Reactive State & Handlers
const {
    onConnect,
    onNodeDragStop,
    onNodeClick,
    onEdgeClick,
    onPaneClick,
    findNode,
} = useVueFlow();
const nodes = ref<any[]>([]);
const edges = ref<any[]>([]);

watch(
    currentDiagram,
    (newDiag) => {
        if (!newDiag) {
            nodes.value = [];
            edges.value = [];
            return;
        }

        nodes.value = (newDiag.nodes || []).map((n: any) => ({
            id: String(n.id),
            type: n.type || 'service',
            label: n.label,
            position: { x: n.position_x, y: n.position_y },
            data: {
                label: n.label,
                type: n.type || 'service',
                metadata: n.metadata || {},
            },
        }));

        edges.value = (newDiag.edges || []).map((e: any) => ({
            id: String(e.id),
            source: String(e.source_node_id),
            target: String(e.target_node_id),
            sourceHandle: e.source_handle || undefined,
            targetHandle: e.target_handle || undefined,
            label: e.label || '',
            animated: true,
            style: { stroke: '#06b6d4', strokeWidth: 2 },
            data: {
                label: e.label || '',
                type: e.type || 'HTTP',
            },
        }));
    },
    { immediate: true },
);

// Handle Drag & Drop position sync
onNodeDragStop(async (e) => {
    const node = e.node;
    if (!node || !activeDiagramId.value) return;

    isSaving.value = true;
    saveMessage.value = 'Saving position...';
    try {
        await updateNodePositionMut({
            id: node.id,
            position_x: Math.round(node.position.x),
            position_y: Math.round(node.position.y),
        });
        saveMessage.value = 'Position saved';
    } catch (err) {
        console.error('Failed to update node position:', err);
        saveMessage.value = 'Position sync error';
    } finally {
        isSaving.value = false;
    }
});

// Handle Edge Connection Creation
onConnect(async (params) => {
    if (!activeDiagramId.value || !params.source || !params.target) return;

    // Enforce 1 relation between two nodes: remove any previous edge between them
    const existingEdgeIndex = edges.value.findIndex(
        (e: any) =>
            (String(e.source) === String(params.source) &&
                String(e.target) === String(params.target)) ||
            (String(e.source) === String(params.target) &&
                String(e.target) === String(params.source)),
    );

    let existingEdgeId: string | null = null;
    if (existingEdgeIndex !== -1) {
        existingEdgeId = edges.value[existingEdgeIndex].id;
        edges.value.splice(existingEdgeIndex, 1);
    }

    // Immediately reflect new connection locally with exact dragged handles
    const tempEdgeId = `temp-${Date.now()}`;
    edges.value.push({
        id: tempEdgeId,
        source: String(params.source),
        target: String(params.target),
        sourceHandle: params.sourceHandle || undefined,
        targetHandle: params.targetHandle || undefined,
        label: 'REST API',
        animated: true,
        style: { stroke: '#06b6d4', strokeWidth: 2 },
        data: {
            label: 'REST API',
            type: 'HTTP',
        },
    });

    isSaving.value = true;
    saveMessage.value = 'Updating connection...';
    try {
        if (existingEdgeId && !existingEdgeId.startsWith('temp-')) {
            await deleteEdgeMut({ id: existingEdgeId });
        }

        await createEdgeMut({
            diagram_id: activeDiagramId.value,
            source_node_id: params.source,
            target_node_id: params.target,
            source_handle: params.sourceHandle || null,
            target_handle: params.targetHandle || null,
            label: 'REST API',
            type: 'HTTP',
        });
        await refetchDiagram();
        saveMessage.value = 'Connection updated';
    } catch (err) {
        console.error('Failed to create edge:', err);
    } finally {
        isSaving.value = false;
    }
});

// Handle Canvas Element Selection
onNodeClick(({ node }) => {
    selectedElement.value = {
        type: 'node',
        item: {
            id: node.id,
            label: (node.data?.label || node.label || '') as string,
            type: (node.data?.type || node.type || 'service') as string,
            metadata: { ...node.data?.metadata },
        },
    };
});

onEdgeClick(({ edge }) => {
    selectedElement.value = {
        type: 'edge',
        item: {
            id: edge.id,
            label: (edge.label || edge.data?.label || '') as string,
            type: (edge.data?.type || 'HTTP') as string,
        },
    };
});

onPaneClick(() => {
    selectedElement.value = null;
});

// Node & Edge Inspector Actions
async function addNode(type: string, defaultLabel: string) {
    if (!activeDiagramId.value) return;
    isSaving.value = true;
    saveMessage.value = 'Adding node...';
    try {
        await createNodeMut({
            diagram_id: activeDiagramId.value,
            label: defaultLabel,
            type: type,
            position_x: Math.floor(Math.random() * 300) + 100,
            position_y: Math.floor(Math.random() * 300) + 100,
            metadata: { technology: 'Laravel / Vue.js', port: '8080' },
        });
        await refetchDiagram();
        saveMessage.value = 'Node added';
    } catch (e) {
        console.error('Failed to add node:', e);
    } finally {
        isSaving.value = false;
    }
}

async function saveNodeDetails() {
    if (!selectedElement.value || selectedElement.value.type !== 'node') return;

    const item = selectedElement.value.item;
    isSaving.value = true;
    saveMessage.value = 'Saving node details...';
    try {
        const flowNode = findNode(item.id);
        if (flowNode) {
            flowNode.label = item.label;
            flowNode.data = {
                ...flowNode.data,
                label: item.label,
                type: item.type,
                metadata: { ...item.metadata },
            };
        }

        await updateNodeMut({
            id: item.id,
            label: item.label,
            type: item.type,
            metadata: item.metadata,
        });

        saveMessage.value = 'Node updated';
    } catch (e) {
        console.error('Failed to update node:', e);
    } finally {
        isSaving.value = false;
    }
}

function promptDeleteNode() {
    if (!selectedElement.value || selectedElement.value.type !== 'node') return;
    showDeleteNodeModal.value = true;
}

async function confirmDeleteNode() {
    if (!selectedElement.value || selectedElement.value.type !== 'node') return;

    const id = selectedElement.value.item.id;
    isSaving.value = true;
    saveMessage.value = 'Deleting node...';
    try {
        await deleteNodeMut({ id });
        showDeleteNodeModal.value = false;
        selectedElement.value = null;
        await refetchDiagram();
        saveMessage.value = 'Node deleted';
    } catch (e) {
        console.error('Failed to delete node:', e);
    } finally {
        isSaving.value = false;
    }
}

async function saveEdgeDetails() {
    if (!selectedElement.value || selectedElement.value.type !== 'edge') return;

    const item = selectedElement.value.item;
    isSaving.value = true;
    saveMessage.value = 'Saving connection...';
    try {
        await updateEdgeMut({
            id: item.id,
            label: item.label,
            type: item.type,
        });
        await refetchDiagram();
        saveMessage.value = 'Connection updated';
    } catch (e) {
        console.error('Failed to update edge:', e);
    } finally {
        isSaving.value = false;
    }
}

async function deleteSelectedEdge() {
    if (!selectedElement.value || selectedElement.value.type !== 'edge') return;

    const id = selectedElement.value.item.id;
    isSaving.value = true;
    saveMessage.value = 'Deleting edge...';
    try {
        await deleteEdgeMut({ id });
        selectedElement.value = null;
        await refetchDiagram();
        saveMessage.value = 'Edge deleted';
    } catch (e) {
        console.error('Failed to delete edge:', e);
    } finally {
        isSaving.value = false;
    }
}

// Create Team
async function createTeam() {
    if (!newTeamName.value) return;

    createTeamError.value = '';
    try {
        const res = await createTeamMut({
            name: newTeamName.value,
            description: newTeamDesc.value,
            user_id: authUser.value?.id ? String(authUser.value.id) : null,
        });
        await refetchTeams();
        if (res?.data?.createTeam?.id) {
            activeTeamId.value = String(res.data.createTeam.id);
        }

        showNewTeamModal.value = false;
        newTeamName.value = '';
        newTeamDesc.value = '';
    } catch (e: any) {
        console.error('Failed to create team:', e);
        const errMsg =
            e.graphQLErrors?.[0]?.message ||
            e.message ||
            'Failed to create team workspace.';
        createTeamError.value = errMsg;
    }
}

// Edit Team
function openEditTeamModal() {
    const currentTeam = teams.value.find(
        (t: any) => String(t.id) === activeTeamId.value,
    );
    if (currentTeam) {
        editTeamName.value = currentTeam.name;
        editTeamDesc.value = currentTeam.description || '';
        editTeamError.value = '';
        showEditTeamModal.value = true;
    }
}

async function updateTeam() {
    if (!editTeamName.value || !activeTeamId.value) return;

    editTeamError.value = '';
    try {
        await updateTeamMut({
            id: activeTeamId.value,
            name: editTeamName.value,
            description: editTeamDesc.value,
        });
        await refetchTeams();
        showEditTeamModal.value = false;
        saveMessage.value = 'Workspace name updated';
    } catch (e: any) {
        console.error('Failed to update team:', e);
        const errMsg =
            e.graphQLErrors?.[0]?.message ||
            e.message ||
            'Failed to update workspace name.';
        editTeamError.value = errMsg;
    }
}

// Manage Team Members
function openTeamMembersModal() {
    inviteMemberEmail.value = '';
    inviteMemberError.value = '';
    inviteMemberSuccess.value = '';
    showTeamMembersModal.value = true;
}

async function addTeamMember() {
    if (!inviteMemberEmail.value || !activeTeamId.value) return;

    inviteMemberError.value = '';
    inviteMemberSuccess.value = '';
    try {
        await addTeamMemberMut({
            team_id: activeTeamId.value,
            email: inviteMemberEmail.value,
        });
        await refetchTeams();
        inviteMemberSuccess.value = `Successfully added ${inviteMemberEmail.value} to team!`;
        inviteMemberEmail.value = '';
    } catch (e: any) {
        console.error('Failed to add team member:', e);
        const errMsg =
            e.graphQLErrors?.[0]?.message ||
            e.message ||
            'Failed to add team member.';
        inviteMemberError.value = errMsg;
    }
}

// Create Project
async function createProject() {
    if (!newProjectTitle.value) return;

    createProjectError.value = '';
    try {
        let teamId = activeTeamId.value;
        if (!teamId) {
            if (teams.value.length > 0) {
                teamId = String(teams.value[0].id);
                activeTeamId.value = teamId;
            } else {
                const teamRes = await createTeamMut({
                    name: `${authUser.value?.name || 'Personal'}'s Workspace`,
                    user_id: authUser.value?.id
                        ? String(authUser.value.id)
                        : null,
                });
                await refetchTeams();
                teamId = String(teamRes?.data?.createTeam?.id);
                activeTeamId.value = teamId;
            }
        }

        const res = await createProjectMut({
            title: newProjectTitle.value,
            description: newProjectDesc.value,
            user_id: authUser.value?.id ? String(authUser.value.id) : null,
            team_id: teamId || null,
        });
        await refetchProjects();
        if (res?.data?.createProject?.id) {
            const diagRes = await createDiagramMut({
                project_id: res.data.createProject.id,
                title: 'Main Diagram',
            });
            await refetchProjects();
            if (diagRes?.data?.createDiagram?.id) {
                activeDiagramId.value = String(diagRes.data.createDiagram.id);
            }
        }

        showNewProjectModal.value = false;
        newProjectTitle.value = '';
        newProjectDesc.value = '';
    } catch (e: any) {
        console.error('Failed to create project:', e);
        const errMsg =
            e.graphQLErrors?.[0]?.message ||
            e.message ||
            'A project with this title already exists in this workspace.';
        createProjectError.value = errMsg;
    }
}

// External Project Sharing
function openShareProjectModal(proj: any) {
    selectedProjectToShare.value = proj;
    shareEmail.value = '';
    shareProjectError.value = '';
    shareProjectSuccess.value = '';
    showShareProjectModal.value = true;
}

async function shareProject() {
    if (!shareEmail.value || !selectedProjectToShare.value) return;

    shareProjectError.value = '';
    shareProjectSuccess.value = '';
    try {
        const res = await shareProjectMut({
            project_id: selectedProjectToShare.value.id,
            email: shareEmail.value,
        });
        await refetchProjects();
        await refetchSharedProjects();
        if (res?.data?.shareProject) {
            selectedProjectToShare.value = res.data.shareProject;
        }

        shareProjectSuccess.value = `Successfully shared project with ${shareEmail.value}!`;
        shareEmail.value = '';
    } catch (e: any) {
        console.error('Failed to share project:', e);
        const errMsg =
            e.graphQLErrors?.[0]?.message ||
            e.message ||
            'Failed to share project.';
        shareProjectError.value = errMsg;
    }
}

async function revokeProjectAccess(userId: string) {
    if (!selectedProjectToShare.value) return;

    shareProjectError.value = '';
    shareProjectSuccess.value = '';
    try {
        const res = await revokeProjectAccessMut({
            project_id: selectedProjectToShare.value.id,
            user_id: userId,
        });
        await refetchProjects();
        await refetchSharedProjects();
        if (res?.data?.revokeProjectAccess) {
            selectedProjectToShare.value = res.data.revokeProjectAccess;
        }

        shareProjectSuccess.value = 'Access revoked successfully.';
    } catch (e: any) {
        console.error('Failed to revoke access:', e);
        const errMsg =
            e.graphQLErrors?.[0]?.message ||
            e.message ||
            'Failed to revoke access.';
        shareProjectError.value = errMsg;
    }
}

// Create Diagram
async function createDiagram() {
    if (!newDiagramTitle.value) return;

    createDiagramError.value = '';
    try {
        let projId =
            currentDiagram.value?.project_id ||
            (projects.value.length > 0 ? projects.value[0].id : null);
        if (!projId) {
            const projRes = await createProjectMut({
                title: 'Architecture Workspace',
                team_id: activeTeamId.value || null,
            });
            await refetchProjects();
            projId = projRes?.data?.createProject?.id;
        }

        if (!projId) return;

        const res = await createDiagramMut({
            project_id: projId,
            title: newDiagramTitle.value,
            description: newDiagramDesc.value,
        });
        await refetchProjects();
        if (res?.data?.createDiagram?.id) {
            activeDiagramId.value = String(res.data.createDiagram.id);
        }

        showNewDiagramModal.value = false;
        newDiagramTitle.value = '';
        newDiagramDesc.value = '';
    } catch (e: any) {
        console.error('Failed to create diagram:', e);
        const errMsg =
            e.graphQLErrors?.[0]?.message ||
            e.message ||
            'A diagram board with this title already exists in this project.';
        createDiagramError.value = errMsg;
    }
}

// Rename Board
function openEditDiagramModal() {
    if (!currentDiagram.value) return;

    editDiagramTitle.value = currentDiagram.value.title;
    editDiagramDesc.value = currentDiagram.value.description || '';
    editDiagramError.value = '';
    showEditDiagramModal.value = true;
}

async function updateDiagram() {
    if (!editDiagramTitle.value || !activeDiagramId.value) return;

    editDiagramError.value = '';
    try {
        await updateDiagramMut({
            id: activeDiagramId.value,
            title: editDiagramTitle.value,
            description: editDiagramDesc.value,
        });
        await refetchProjects();
        await refetchSharedProjects();
        await refetchDiagram();
        showEditDiagramModal.value = false;
        saveMessage.value = 'Board title updated';
    } catch (e: any) {
        console.error('Failed to update diagram:', e);
        const errMsg =
            e.graphQLErrors?.[0]?.message ||
            e.message ||
            'A diagram board with this title already exists in this project.';
        editDiagramError.value = errMsg;
    }
}

function miniMapNodeColor(node: any) {
    const type = node.data?.type || node.type;
    switch (type) {
        case 'database':
            return '#1e1b4b';
        case 'queue':
            return '#451a03';
        case 'client':
            return '#3b0764';
        case 'external_api':
            return '#4c0519';
        case 'service':
        default:
            return '#064e3b';
    }
}

function miniMapNodeStroke(node: any) {
    const type = node.data?.type || node.type;
    switch (type) {
        case 'database':
            return '#6366f1';
        case 'queue':
            return '#f59e0b';
        case 'client':
            return '#a855f7';
        case 'external_api':
            return '#f43f5e';
        case 'service':
        default:
            return '#10b981';
    }
}

function handleLogout() {
    router.post('/logout');
}
</script>

<template>
    <div
        class="flex h-screen w-screen flex-col overflow-hidden bg-black font-sans text-neutral-100"
    >
        <!-- TOP NAVIGATION HEADER -->
        <DiagramHeader
            v-model:active-team-id="activeTeamId"
            v-model:active-diagram-id="activeDiagramId"
            :team-options="teamOptions"
            :board-options="boardOptions"
            :auth-user="authUser"
            :is-saving="isSaving"
            :save-message="saveMessage"
            @open-edit-team="openEditTeamModal"
            @open-team-members="openTeamMembersModal"
            @open-new-team="showNewTeamModal = true"
            @open-edit-diagram="openEditDiagramModal"
            @open-new-project="showNewProjectModal = true"
            @open-export="showExportModal = true"
            @logout="handleLogout"
        />

        <!-- MAIN WORKSPACE -->
        <div class="relative flex flex-1 overflow-hidden">
            <!-- LEFT SIDEBAR: PALETTE & PROJECTS -->
            <aside
                class="z-20 flex w-64 shrink-0 flex-col border-r border-neutral-800 bg-black"
            >
                <!-- Node Palette Section -->
                <div
                    class="flex items-center justify-between border-b border-neutral-800 p-4"
                >
                    <span
                        class="text-xs font-semibold tracking-wider text-neutral-400 uppercase"
                        >Node Palette</span
                    >
                    <button
                        @click="showNewDiagramModal = true"
                        title="Add Diagram Board"
                        class="rounded p-1 text-neutral-400 hover:bg-neutral-900 hover:text-white"
                    >
                        <FolderPlus class="h-4 w-4" />
                    </button>
                </div>

                <div class="space-y-2.5 border-b border-neutral-800 p-4">
                    <button
                        @click="addNode('service', 'Microservice API')"
                        class="group flex w-full items-center gap-3 rounded-lg border border-neutral-800 bg-neutral-950 p-2.5 text-left transition hover:border-emerald-500/50 hover:bg-neutral-900"
                    >
                        <div
                            class="rounded-md border border-emerald-800/40 bg-emerald-950/60 p-1.5 text-emerald-400"
                        >
                            <Server class="h-4 w-4" />
                        </div>
                        <div>
                            <div
                                class="text-xs font-semibold text-neutral-200 group-hover:text-emerald-300"
                            >
                                Microservice
                            </div>
                            <div class="text-[10px] text-neutral-500">
                                REST / gRPC Service
                            </div>
                        </div>
                    </button>

                    <button
                        @click="addNode('database', 'PostgreSQL DB')"
                        class="group flex w-full items-center gap-3 rounded-lg border border-neutral-800 bg-neutral-950 p-2.5 text-left transition hover:border-indigo-500/50 hover:bg-neutral-900"
                    >
                        <div
                            class="rounded-md border border-indigo-800/40 bg-indigo-950/60 p-1.5 text-indigo-400"
                        >
                            <Database class="h-4 w-4" />
                        </div>
                        <div>
                            <div
                                class="text-xs font-semibold text-neutral-200 group-hover:text-indigo-300"
                            >
                                Database
                            </div>
                            <div class="text-[10px] text-neutral-500">
                                SQL / NoSQL Data Store
                            </div>
                        </div>
                    </button>

                    <button
                        @click="addNode('queue', 'Kafka Queue')"
                        class="group flex w-full items-center gap-3 rounded-lg border border-neutral-800 bg-neutral-950 p-2.5 text-left transition hover:border-amber-500/50 hover:bg-neutral-900"
                    >
                        <div
                            class="rounded-md border border-amber-800/40 bg-amber-950/60 p-1.5 text-amber-400"
                        >
                            <Layers class="h-4 w-4" />
                        </div>
                        <div>
                            <div
                                class="text-xs font-semibold text-neutral-200 group-hover:text-amber-300"
                            >
                                Message Queue
                            </div>
                            <div class="text-[10px] text-neutral-500">
                                Kafka / RabbitMQ
                            </div>
                        </div>
                    </button>

                    <button
                        @click="addNode('client', 'Web Frontend App')"
                        class="group flex w-full items-center gap-3 rounded-lg border border-neutral-800 bg-neutral-950 p-2.5 text-left transition hover:border-purple-500/50 hover:bg-neutral-900"
                    >
                        <div
                            class="rounded-md border border-purple-800/40 bg-purple-950/60 p-1.5 text-purple-400"
                        >
                            <Monitor class="h-4 w-4" />
                        </div>
                        <div>
                            <div
                                class="text-xs font-semibold text-neutral-200 group-hover:text-purple-300"
                            >
                                Client App
                            </div>
                            <div class="text-[10px] text-neutral-500">
                                Web / Mobile Client
                            </div>
                        </div>
                    </button>

                    <button
                        @click="addNode('external_api', 'Stripe Gateway')"
                        class="group flex w-full items-center gap-3 rounded-lg border border-neutral-800 bg-neutral-950 p-2.5 text-left transition hover:border-rose-500/50 hover:bg-neutral-900"
                    >
                        <div
                            class="rounded-md border border-rose-800/40 bg-rose-950/60 p-1.5 text-rose-400"
                        >
                            <Globe class="h-4 w-4" />
                        </div>
                        <div>
                            <div
                                class="text-xs font-semibold text-neutral-200 group-hover:text-rose-300"
                            >
                                External API
                            </div>
                            <div class="text-[10px] text-neutral-500">
                                Cloud API Gateway
                            </div>
                        </div>
                    </button>
                </div>

                <!-- Projects Tree & Boards Sidebar Section -->
                <div
                    class="flex items-center justify-between border-b border-neutral-800 p-4"
                >
                    <span
                        class="text-xs font-semibold tracking-wider text-neutral-400 uppercase"
                        >Workspace Projects</span
                    >
                    <button
                        @click="showNewProjectModal = true"
                        title="Create New Project"
                        class="rounded p-1 text-neutral-400 hover:bg-neutral-900 hover:text-white"
                    >
                        <Plus class="h-4 w-4" />
                    </button>
                </div>

                <div class="flex-1 space-y-4 overflow-y-auto p-4">
                    <!-- Workspace Team Projects -->
                    <div
                        v-for="proj in projects"
                        :key="proj.id"
                        class="space-y-1"
                    >
                        <div class="group flex items-center justify-between">
                            <span
                                class="truncate text-xs font-bold text-neutral-300"
                                >{{ proj.title }}</span
                            >
                            <div
                                class="flex items-center gap-1 opacity-0 transition group-hover:opacity-100"
                            >
                                <button
                                    @click="openShareProjectModal(proj)"
                                    title="Share Project Externally"
                                    class="rounded p-1 text-neutral-400 hover:bg-neutral-900 hover:text-indigo-400"
                                >
                                    <Share2 class="h-3 w-3" />
                                </button>
                                <button
                                    @click="showNewDiagramModal = true"
                                    title="Add Diagram Board"
                                    class="rounded p-1 text-neutral-400 hover:bg-neutral-900 hover:text-cyan-400"
                                >
                                    <Plus class="h-3 w-3" />
                                </button>
                            </div>
                        </div>

                        <div
                            class="ml-1 space-y-1 border-l border-neutral-800 pl-2"
                        >
                            <button
                                v-for="diag in proj.diagrams"
                                :key="diag.id"
                                @click="activeDiagramId = String(diag.id)"
                                class="flex w-full items-center justify-between rounded px-2 py-1.5 text-left text-xs transition"
                                :class="
                                    activeDiagramId === String(diag.id)
                                        ? 'border border-cyan-800/40 bg-cyan-950/40 font-semibold text-cyan-300'
                                        : 'text-neutral-400 hover:bg-neutral-900 hover:text-neutral-200'
                                "
                            >
                                <span class="truncate">{{ diag.title }}</span>
                                <ChevronRight
                                    class="h-3 w-3 shrink-0 text-neutral-600"
                                />
                            </button>
                        </div>
                    </div>

                    <!-- External Shared Projects -->
                    <div
                        v-if="sharedProjects.length"
                        class="border-t border-neutral-900 pt-2"
                    >
                        <span
                            class="mb-2 block text-[11px] font-semibold tracking-wider text-neutral-500 uppercase"
                            >Shared External Projects</span
                        >
                        <div
                            v-for="proj in sharedProjects"
                            :key="'shared-' + proj.id"
                            class="space-y-1"
                        >
                            <div class="flex items-center justify-between">
                                <span
                                    class="truncate text-xs font-bold text-indigo-300"
                                    >{{ proj.title }}</span
                                >
                                <span
                                    class="rounded border border-indigo-800/40 bg-indigo-950 px-1.5 py-0.5 text-[9px] text-indigo-400 uppercase"
                                    >External</span
                                >
                            </div>
                            <div
                                class="ml-1 space-y-1 border-l border-neutral-800 pl-2"
                            >
                                <button
                                    v-for="diag in proj.diagrams"
                                    :key="diag.id"
                                    @click="activeDiagramId = String(diag.id)"
                                    class="flex w-full items-center justify-between rounded px-2 py-1.5 text-left text-xs transition"
                                    :class="
                                        activeDiagramId === String(diag.id)
                                            ? 'border border-indigo-800/40 bg-indigo-950/40 font-semibold text-indigo-300'
                                            : 'text-neutral-400 hover:bg-neutral-900 hover:text-neutral-200'
                                    "
                                >
                                    <span class="truncate">{{
                                        diag.title
                                    }}</span>
                                    <ChevronRight
                                        class="h-3 w-3 shrink-0 text-neutral-600"
                                    />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- CENTER VUE FLOW CANVAS -->
            <main class="relative h-full flex-1 bg-black">
                <div
                    v-if="diagramLoading"
                    class="absolute inset-0 z-10 flex items-center justify-center bg-black/90 backdrop-blur-sm"
                >
                    <div
                        class="flex items-center gap-3 text-sm font-medium text-cyan-400"
                    >
                        <Loader2 class="h-6 w-6 animate-spin" />
                        <span>Connecting nodemap Live Collaboration...</span>
                    </div>
                </div>

                <!-- Empty State Canvas Overlay -->
                <div
                    v-if="!diagramLoading && !nodes.length"
                    class="pointer-events-none absolute inset-0 z-10 flex flex-col items-center justify-center bg-black/90 p-6 text-center"
                >
                    <div
                        class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl border border-neutral-800 bg-neutral-900 text-cyan-400 shadow-2xl"
                    >
                        <Sparkles class="h-8 w-8" />
                    </div>
                    <h3
                        class="mb-2 text-xl font-extrabold tracking-tight text-white"
                    >
                        Pristine Architecture Canvas
                    </h3>
                    <p class="mb-6 max-w-sm text-xs text-neutral-400">
                        Your diagram board is completely empty. Click any node
                        from the left palette to start building your
                        microservices topology.
                    </p>
                    <div class="pointer-events-auto flex items-center gap-3">
                        <button
                            @click="addNode('service', 'API Gateway')"
                            class="rounded-xl bg-cyan-500 px-4 py-2 text-xs font-semibold text-black shadow-lg shadow-cyan-500/20 transition hover:bg-cyan-400"
                        >
                            + Add Microservice Node
                        </button>
                        <button
                            @click="showNewProjectModal = true"
                            class="rounded-xl border border-neutral-800 bg-neutral-900 px-4 py-2 text-xs font-semibold text-neutral-200 transition hover:bg-neutral-800"
                        >
                            + New Project
                        </button>
                    </div>
                </div>

                <VueFlow
                    v-model:nodes="nodes"
                    v-model:edges="edges"
                    :node-types="nodeTypes"
                    fit-view-on-init
                    class="h-full w-full"
                >
                    <Background pattern-color="#262626" :gap="24" />
                    <Controls
                        class="!border-neutral-800 !bg-neutral-900 !text-neutral-100"
                    />
                    <MiniMap
                        class="overflow-hidden !rounded-xl !border-2 !border-white !bg-black shadow-2xl"
                        mask-color="rgba(0, 0, 0, 0.85)"
                        :node-color="miniMapNodeColor"
                        :node-stroke-color="miniMapNodeStroke"
                        :node-border-radius="6"
                    />
                </VueFlow>
            </main>

            <!-- RIGHT INSPECTOR SIDEBAR -->
            <NodeInspector
                :selected-element="selectedElement"
                @close="selectedElement = null"
                @save-node="saveNodeDetails"
                @prompt-delete-node="promptDeleteNode"
                @save-edge="saveEdgeDetails"
                @delete-edge="deleteSelectedEdge"
            />
        </div>

        <!-- MODAL DIALOGS -->
        <ExportModal
            :show="showExportModal"
            :current-diagram="currentDiagram"
            @close="showExportModal = false"
        />

        <DeleteNodeModal
            :show="showDeleteNodeModal"
            :node-label="selectedElement?.item?.label"
            @close="showDeleteNodeModal = false"
            @confirm="confirmDeleteNode"
        />

        <TeamModal
            :show-new-team-modal="showNewTeamModal"
            :show-edit-team-modal="showEditTeamModal"
            :show-team-members-modal="showTeamMembersModal"
            :current-active-team="currentActiveTeam"
            v-model:new-team-name="newTeamName"
            v-model:new-team-desc="newTeamDesc"
            :create-team-error="createTeamError"
            v-model:edit-team-name="editTeamName"
            v-model:edit-team-desc="editTeamDesc"
            :edit-team-error="editTeamError"
            v-model:invite-member-email="inviteMemberEmail"
            :invite-member-error="inviteMemberError"
            :invite-member-success="inviteMemberSuccess"
            @close-new-team="showNewTeamModal = false"
            @close-edit-team="showEditTeamModal = false"
            @close-team-members="showTeamMembersModal = false"
            @create-team="createTeam"
            @update-team="updateTeam"
            @add-team-member="addTeamMember"
        />

        <ProjectModal
            :show-new-project-modal="showNewProjectModal"
            :show-share-project-modal="showShareProjectModal"
            :selected-project-to-share="selectedProjectToShare"
            v-model:new-project-title="newProjectTitle"
            v-model:new-project-desc="newProjectDesc"
            :create-project-error="createProjectError"
            v-model:share-email="shareEmail"
            :share-project-error="shareProjectError"
            :share-project-success="shareProjectSuccess"
            @close-new-project="showNewProjectModal = false"
            @close-share-project="showShareProjectModal = false"
            @create-project="createProject"
            @share-project="shareProject"
            @revoke-project-access="revokeProjectAccess"
        />

        <DiagramModal
            :show-new-diagram-modal="showNewDiagramModal"
            :show-edit-diagram-modal="showEditDiagramModal"
            v-model:new-diagram-title="newDiagramTitle"
            v-model:new-diagram-desc="newDiagramDesc"
            :create-diagram-error="createDiagramError"
            v-model:edit-diagram-title="editDiagramTitle"
            v-model:edit-diagram-desc="editDiagramDesc"
            :edit-diagram-error="editDiagramError"
            @close-new-diagram="showNewDiagramModal = false"
            @close-edit-diagram="showEditDiagramModal = false"
            @create-diagram="createDiagram"
            @update-diagram="updateDiagram"
        />
    </div>
</template>
