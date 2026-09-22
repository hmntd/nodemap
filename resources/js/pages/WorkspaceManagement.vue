<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { useQuery, useMutation } from '@vue/apollo-composable';
import { router, usePage, Link } from '@inertiajs/vue3';
import {
    GET_TEAMS,
    CREATE_TEAM,
    UPDATE_TEAM,
    DELETE_TEAM,
    ADD_TEAM_MEMBER,
    REMOVE_TEAM_MEMBER,
    UPDATE_TEAM_MEMBER_ROLE
} from '@/graphql/queries';
import {
    Sparkles,
    Users,
    Crown,
    Shield,
    User,
    UserPlus,
    Trash2,
    Pencil,
    Plus,
    LayoutGrid,
    Check,
    Loader2,
    LogOut,
    AlertCircle,
    ShieldAlert,
    X
} from '@lucide/vue';

const page = usePage();
const authUser = computed(() => page.props.auth?.user || null);

const selectedTeamId = ref<string>('');

// Modals
const showNewTeamModal = ref(false);
const showEditTeamModal = ref(false);

// Form States & Errors
const newTeamName = ref('');
const newTeamDesc = ref('');
const createTeamError = ref('');

const editTeamName = ref('');
const editTeamDesc = ref('');
const editTeamError = ref('');

const inviteEmail = ref('');
const inviteRole = ref<'user' | 'admin'>('user');
const inviteError = ref('');
const inviteSuccess = ref('');

const actionError = ref('');
const actionSuccess = ref('');

// Apollo Queries & Mutations
const { result: teamsResult, loading: teamsLoading, refetch: refetchTeams } = useQuery(GET_TEAMS);
const { mutate: createTeamMut } = useMutation(CREATE_TEAM);
const { mutate: updateTeamMut } = useMutation(UPDATE_TEAM);
const { mutate: deleteTeamMut } = useMutation(DELETE_TEAM);

const { mutate: addTeamMemberMut } = useMutation(ADD_TEAM_MEMBER);
const { mutate: removeTeamMemberMut } = useMutation(REMOVE_TEAM_MEMBER);
const { mutate: updateTeamMemberRoleMut } = useMutation(UPDATE_TEAM_MEMBER_ROLE);

const teams = computed(() => teamsResult.value?.teams || []);

// Auto select first team
watch(teamsResult, (val) => {
    if (val?.teams?.length && !selectedTeamId.value) {
        selectedTeamId.value = String(val.teams[0].id);
    }
}, { immediate: true });

const selectedTeam = computed(() => {
    return teams.value.find((t: any) => String(t.id) === selectedTeamId.value) || null;
});

// Compute active user's role in selected team: 'creator' | 'admin' | 'user'
const currentUserRole = computed<'creator' | 'admin' | 'user'>(() => {
    if (!selectedTeam.value || !authUser.value) return 'user';

    if (String(selectedTeam.value.user_id) === String(authUser.value.id)) {
        return 'creator';
    }

    const member = (selectedTeam.value.membersWithRoles || []).find(
        (m: any) => String(m.id) === String(authUser.value?.id)
    );
    if (member && member.role === 'admin') {
        return 'admin';
    }

    return 'user';
});

// Actions
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
            selectedTeamId.value = String(res.data.createTeam.id);
        }
        showNewTeamModal.value = false;
        newTeamName.value = '';
        newTeamDesc.value = '';
    } catch (e: any) {
        console.error('Failed to create team:', e);
        const errMsg = e.graphQLErrors?.[0]?.message || e.message || 'Failed to create workspace.';
        createTeamError.value = errMsg;
    }
}

function openEditTeamModal() {
    if (!selectedTeam.value) return;

    editTeamName.value = selectedTeam.value.name;
    editTeamDesc.value = selectedTeam.value.description || '';
    editTeamError.value = '';
    showEditTeamModal.value = true;
}

async function updateTeam() {
    if (!editTeamName.value || !selectedTeamId.value) return;

    editTeamError.value = '';
    try {
        await updateTeamMut({
            id: selectedTeamId.value,
            name: editTeamName.value,
            description: editTeamDesc.value,
        });
        await refetchTeams();
        showEditTeamModal.value = false;
        actionSuccess.value = 'Workspace updated successfully!';
        setTimeout(() => { actionSuccess.value = ''; }, 3000);
    } catch (e: any) {
        console.error('Failed to update team:', e);
        const errMsg = e.graphQLErrors?.[0]?.message || e.message || 'Failed to update workspace.';
        editTeamError.value = errMsg;
    }
}

async function deleteTeam() {
    if (!selectedTeamId.value || currentUserRole.value !== 'creator') return;

    if (!confirm(`Are you sure you want to delete workspace "${selectedTeam.value?.name}"? All associated projects will be removed.`)) return;

    actionError.value = '';
    try {
        await deleteTeamMut({ id: selectedTeamId.value });
        selectedTeamId.value = '';
        await refetchTeams();
        actionSuccess.value = 'Workspace deleted successfully.';
        setTimeout(() => { actionSuccess.value = ''; }, 3000);
    } catch (e: any) {
        console.error('Failed to delete team:', e);
        const errMsg = e.graphQLErrors?.[0]?.message || e.message || 'Failed to delete workspace.';
        actionError.value = errMsg;
    }
}

async function inviteMember() {
    if (!inviteEmail.value || !selectedTeamId.value) return;

    inviteError.value = '';
    inviteSuccess.value = '';
    try {
        await addTeamMemberMut({
            team_id: selectedTeamId.value,
            email: inviteEmail.value,
            role: inviteRole.value,
        });
        await refetchTeams();
        inviteSuccess.value = `Successfully invited ${inviteEmail.value} as ${inviteRole.value.toUpperCase()}!`;
        inviteEmail.value = '';
        setTimeout(() => { inviteSuccess.value = ''; }, 4000);
    } catch (e: any) {
        console.error('Failed to invite member:', e);
        const errMsg = e.graphQLErrors?.[0]?.message || e.message || 'Failed to invite member.';
        inviteError.value = errMsg;
    }
}

async function removeMember(userId: string, memberName: string) {
    if (!selectedTeamId.value) return;

    if (!confirm(`Are you sure you want to remove ${memberName} from this workspace?`)) return;

    actionError.value = '';
    actionSuccess.value = '';
    try {
        await removeTeamMemberMut({
            team_id: selectedTeamId.value,
            user_id: userId,
        });
        await refetchTeams();
        actionSuccess.value = `Removed ${memberName} from workspace.`;
        setTimeout(() => { actionSuccess.value = ''; }, 3000);
    } catch (e: any) {
        console.error('Failed to remove member:', e);
        const errMsg = e.graphQLErrors?.[0]?.message || e.message || 'Failed to remove member.';
        actionError.value = errMsg;
    }
}

async function updateMemberRole(userId: string, newRole: string) {
    if (!selectedTeamId.value || currentUserRole.value !== 'creator') return;

    actionError.value = '';
    actionSuccess.value = '';
    try {
        await updateTeamMemberRoleMut({
            team_id: selectedTeamId.value,
            user_id: userId,
            role: newRole,
        });
        await refetchTeams();
        actionSuccess.value = `Member role updated to ${newRole.toUpperCase()}.`;
        setTimeout(() => { actionSuccess.value = ''; }, 3000);
    } catch (e: any) {
        console.error('Failed to update member role:', e);
        const errMsg = e.graphQLErrors?.[0]?.message || e.message || 'Failed to update member role.';
        actionError.value = errMsg;
    }
}

function handleLogout() {
    router.post('/logout');
}
</script>

<template>
    <div class="h-screen w-screen flex flex-col bg-black text-neutral-100 font-sans overflow-hidden">
        <!-- TOP NAVIGATION BAR -->
        <header class="h-16 border-b border-neutral-800 bg-black px-6 flex items-center justify-between z-30 shrink-0">
            <div class="flex items-center gap-6">
                <!-- Brand -->
                <Link href="/dashboard" class="flex items-center gap-2.5 group">
                    <div
                        class="w-9 h-9 rounded-xl bg-gradient-to-tr from-cyan-500 via-indigo-500 to-purple-600 p-0.5 shadow-lg shadow-cyan-500/20 group-hover:scale-105 transition-transform">
                        <div class="w-full h-full bg-black rounded-[10px] flex items-center justify-center">
                            <Sparkles class="w-5 h-5 text-cyan-400" />
                        </div>
                    </div>
                    <div>
                        <h1
                            class="font-black text-lg tracking-tight bg-gradient-to-r from-white via-cyan-200 to-indigo-300 bg-clip-text text-transparent">
                            nodemap
                        </h1>
                        <p class="text-[10px] text-neutral-500 font-mono tracking-widest uppercase">Workspace Portal</p>
                    </div>
                </Link>

                <!-- Navigation Tabs -->
                <div
                    class="flex items-center gap-2 bg-neutral-950 border border-neutral-800 p-1 rounded-xl text-xs font-medium">
                    <Link href="/dashboard"
                        class="px-3 py-1.5 rounded-lg text-neutral-400 hover:text-white hover:bg-neutral-900 transition flex items-center gap-1.5">
                        <LayoutGrid class="w-3.5 h-3.5" />
                        <span>Architect Canvas</span>
                    </Link>
                    <div
                        class="px-3 py-1.5 rounded-lg bg-neutral-900 text-cyan-400 font-semibold border border-neutral-800 flex items-center gap-1.5">
                        <Users class="w-3.5 h-3.5" />
                        <span>Workspace Management</span>
                    </div>
                </div>
            </div>

            <!-- Profile & Logout -->
            <div v-if="authUser" class="flex items-center gap-3">
                <div class="text-right hidden md:block">
                    <div class="text-xs font-semibold text-neutral-200">{{ authUser.name }}</div>
                    <div class="text-[10px] text-neutral-500 font-mono">{{ authUser.email }}</div>
                </div>
                <button @click="handleLogout" title="Sign Out"
                    class="p-2 rounded-lg bg-neutral-900 hover:bg-neutral-800 text-neutral-400 hover:text-rose-400 border border-neutral-800 transition">
                    <LogOut class="w-4 h-4" />
                </button>
            </div>
        </header>

        <!-- MAIN LAYOUT -->
        <div class="flex-1 flex overflow-hidden">
            <!-- LEFT SIDEBAR: WORKSPACES SELECTOR LIST -->
            <aside class="w-80 border-r border-neutral-800 bg-black flex flex-col shrink-0">
                <div class="p-4 border-b border-neutral-800 flex items-center justify-between">
                    <span class="text-xs font-semibold text-neutral-400 uppercase tracking-wider">Your Workspaces</span>
                    <button @click="showNewTeamModal = true" title="Create New Workspace"
                        class="px-2.5 py-1 rounded-lg bg-cyan-500 hover:bg-cyan-400 text-black text-xs font-semibold flex items-center gap-1 transition">
                        <Plus class="w-3.5 h-3.5" />
                        <span>New</span>
                    </button>
                </div>

                <div v-if="teamsLoading"
                    class="p-6 text-center text-neutral-500 text-xs flex items-center justify-center gap-2">
                    <Loader2 class="w-4 h-4 animate-spin text-cyan-400" />
                    <span>Loading workspaces...</span>
                </div>

                <div v-else class="flex-1 overflow-y-auto p-3 space-y-2">
                    <button v-for="t in teams" :key="t.id" @click="selectedTeamId = String(t.id)"
                        class="w-full text-left p-3 rounded-xl border transition-all duration-200 group relative"
                        :class="[
                            String(t.id) === selectedTeamId
                                ? 'bg-neutral-900 border-cyan-500/60 text-white shadow-[0_0_15px_rgba(6,182,212,0.15)]'
                                : 'bg-black hover:bg-neutral-900/60 border-neutral-850 hover:border-neutral-700 text-neutral-300'
                        ]">
                        <div class="flex items-center justify-between mb-1">
                            <h3 class="font-bold text-xs truncate group-hover:text-cyan-300 transition-colors">
                                {{ t.name }}
                            </h3>

                            <!-- User Role Badge in this Workspace -->
                            <span v-if="String(t.user_id) === String(authUser?.id)"
                                class="px-1.5 py-0.5 text-[9px] uppercase font-bold rounded bg-amber-950/80 border border-amber-800/60 text-amber-400 flex items-center gap-1">
                                <Crown class="w-2.5 h-2.5" />
                                <span>Creator</span>
                            </span>
                            <span
                                v-else-if="(t.membersWithRoles || []).some((m: any) => String(m.id) === String(authUser?.id) && m.role === 'admin')"
                                class="px-1.5 py-0.5 text-[9px] uppercase font-bold rounded bg-indigo-950/80 border border-indigo-800/60 text-indigo-400 flex items-center gap-1">
                                <Shield class="w-2.5 h-2.5" />
                                <span>Admin</span>
                            </span>
                            <span v-else
                                class="px-1.5 py-0.5 text-[9px] uppercase font-semibold rounded bg-neutral-900 border border-neutral-800 text-neutral-400 flex items-center gap-1">
                                <User class="w-2.5 h-2.5" />
                                <span>User</span>
                            </span>
                        </div>

                        <p v-if="t.description" class="text-[11px] text-neutral-500 truncate mb-2">
                            {{ t.description }}
                        </p>

                        <div class="flex items-center gap-3 text-[10px] text-neutral-400">
                            <span class="flex items-center gap-1">
                                <Users class="w-3 h-3 text-cyan-400" />
                                <span>{{ (t.membersWithRoles || []).length || 1 }} Members</span>
                            </span>
                            <span class="flex items-center gap-1">
                                <LayoutGrid class="w-3 h-3 text-indigo-400" />
                                <span>{{ (t.projects || []).length }} Projects</span>
                            </span>
                        </div>
                    </button>
                </div>
            </aside>

            <!-- RIGHT WORKSPACE MANAGEMENT CONTENT PANEL -->
            <main class="flex-1 overflow-y-auto bg-black p-8 space-y-6">
                <!-- Action Status Banner -->
                <div v-if="actionError"
                    class="p-3.5 rounded-xl bg-rose-950/70 border border-rose-800/60 text-rose-300 text-xs flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <AlertCircle class="w-4 h-4 text-rose-400 shrink-0" />
                        <span>{{ actionError }}</span>
                    </div>
                    <button @click="actionError = ''" class="text-rose-400 hover:text-white">
                        <X class="w-4 h-4" />
                    </button>
                </div>
                <div v-if="actionSuccess"
                    class="p-3.5 rounded-xl bg-emerald-950/70 border border-emerald-800/60 text-emerald-300 text-xs flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <Check class="w-4 h-4 text-emerald-400 shrink-0" />
                        <span>{{ actionSuccess }}</span>
                    </div>
                    <button @click="actionSuccess = ''" class="text-emerald-400 hover:text-white">
                        <X class="w-4 h-4" />
                    </button>
                </div>

                <div v-if="!selectedTeam" class="h-full flex flex-col items-center justify-center text-neutral-500">
                    <Users class="w-12 h-12 text-neutral-700 mb-3" />
                    <p class="text-sm">Select a workspace from the left sidebar to manage team members & roles.</p>
                </div>

                <template v-else>
                    <!-- Workspace Header Banner -->
                    <div
                        class="p-6 rounded-2xl bg-neutral-950 border border-neutral-800 flex items-start justify-between">
                        <div>
                            <div class="flex items-center gap-3 mb-1">
                                <h2 class="text-xl font-extrabold text-white tracking-tight">{{ selectedTeam.name }}
                                </h2>
                                <!-- Current User's Role Pill -->
                                <span v-if="currentUserRole === 'creator'"
                                    class="px-2.5 py-1 rounded-full text-xs font-bold uppercase bg-amber-950/80 border border-amber-800/60 text-amber-400 flex items-center gap-1.5">
                                    <Crown class="w-3.5 h-3.5 text-amber-400" />
                                    <span>Creator Role</span>
                                </span>
                                <span v-else-if="currentUserRole === 'admin'"
                                    class="px-2.5 py-1 rounded-full text-xs font-bold uppercase bg-indigo-950/80 border border-indigo-800/60 text-indigo-400 flex items-center gap-1.5">
                                    <Shield class="w-3.5 h-3.5 text-indigo-400" />
                                    <span>Admin Role</span>
                                </span>
                                <span v-else
                                    class="px-2.5 py-1 rounded-full text-xs font-medium uppercase bg-neutral-900 border border-neutral-800 text-neutral-400 flex items-center gap-1.5">
                                    <User class="w-3.5 h-3.5 text-neutral-400" />
                                    <span>Standard User Role</span>
                                </span>
                            </div>
                            <p class="text-xs text-neutral-400 max-w-xl">
                                {{ selectedTeam.description || 'No description provided for this team workspace.' }}
                            </p>
                        </div>

                        <!-- Workspace Actions -->
                        <div class="flex items-center gap-2">
                            <button v-if="currentUserRole === 'creator' || currentUserRole === 'admin'"
                                @click="openEditTeamModal"
                                class="px-3 py-1.5 rounded-lg bg-neutral-900 hover:bg-neutral-850 border border-neutral-800 text-neutral-300 text-xs font-medium flex items-center gap-1.5 transition">
                                <Pencil class="w-3.5 h-3.5 text-cyan-400" />
                                <span>Edit Name</span>
                            </button>
                            <button v-if="currentUserRole === 'creator'" @click="deleteTeam"
                                class="px-3 py-1.5 rounded-lg bg-rose-950/60 hover:bg-rose-900 border border-rose-800/50 text-rose-300 text-xs font-medium flex items-center gap-1.5 transition">
                                <Trash2 class="w-3.5 h-3.5 text-rose-400" />
                                <span>Delete Workspace</span>
                            </button>
                        </div>
                    </div>

                    <!-- Role Authorization Info Banner -->
                    <div v-if="currentUserRole === 'user'"
                        class="p-4 rounded-xl bg-cyan-950/30 border border-cyan-800/40 text-cyan-300 text-xs flex items-center gap-3">
                        <ShieldAlert class="w-5 h-5 text-cyan-400 shrink-0" />
                        <div>
                            <span class="font-semibold block text-cyan-200">View Only Access Mode</span>
                            <span>As a standard user in this workspace, you can view team members but cannot invite
                                users, change roles, or remove members. Contact an Admin or the Creator to make
                                changes.</span>
                        </div>
                    </div>

                    <!-- INVITE MEMBER CARD (Enabled for Creator & Admin) -->
                    <div v-if="currentUserRole === 'creator' || currentUserRole === 'admin'"
                        class="p-6 rounded-2xl bg-neutral-950 border border-neutral-800 space-y-4">
                        <div class="flex items-center gap-2 border-b border-neutral-850 pb-3">
                            <UserPlus class="w-4 h-4 text-cyan-400" />
                            <h3 class="font-bold text-sm text-neutral-100">Invite New Workspace Member</h3>
                        </div>

                        <div v-if="inviteError"
                            class="p-3 rounded-lg bg-rose-950/60 border border-rose-800/50 text-rose-300 text-xs">
                            {{ inviteError }}
                        </div>
                        <div v-if="inviteSuccess"
                            class="p-3 rounded-lg bg-emerald-950/60 border border-emerald-800/50 text-emerald-300 text-xs">
                            {{ inviteSuccess }}
                        </div>

                        <div class="flex flex-col md:flex-row items-end gap-3">
                            <div class="flex-1 space-y-1 w-full">
                                <label class="block text-xs font-medium text-neutral-400">User Email Address</label>
                                <input v-model="inviteEmail" type="email" placeholder="architect@nodemap.dev"
                                    class="w-full bg-neutral-900 border border-neutral-800 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-cyan-500" />
                            </div>

                            <div v-if="currentUserRole === 'creator'" class="w-full md:w-48 space-y-1">
                                <label class="block text-xs font-medium text-neutral-400">Assign Role</label>
                                <select v-model="inviteRole"
                                    class="w-full bg-neutral-900 border border-neutral-800 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-cyan-500">
                                    <option value="user">User (Standard)</option>
                                    <option value="admin">Admin (Manager)</option>
                                </select>
                            </div>

                            <button @click="inviteMember"
                                class="w-full md:w-auto px-5 py-2 rounded-lg bg-cyan-500 hover:bg-cyan-400 text-black text-xs font-semibold flex items-center justify-center gap-2 transition">
                                <UserPlus class="w-4 h-4" />
                                <span>Invite Member</span>
                            </button>
                        </div>
                    </div>

                    <!-- MEMBERS & ROLES TABLE -->
                    <div class="rounded-2xl bg-neutral-950 border border-neutral-800 overflow-hidden">
                        <div class="p-4 border-b border-neutral-800 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <Users class="w-4 h-4 text-cyan-400" />
                                <h3 class="font-bold text-sm text-white">Workspace Members & Hierarchy</h3>
                            </div>
                            <span class="text-xs text-neutral-500 font-mono">
                                Total: {{ (selectedTeam.membersWithRoles || []).length }} Members
                            </span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse text-xs">
                                <thead>
                                    <tr
                                        class="border-b border-neutral-850 bg-neutral-900/50 text-neutral-400 font-semibold uppercase tracking-wider text-[10px]">
                                        <th class="py-3 px-4">Member</th>
                                        <th class="py-3 px-4">Email</th>
                                        <th class="py-3 px-4">Current Role</th>
                                        <th class="py-3 px-4">Role Action (Creator Only)</th>
                                        <th class="py-3 px-4 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-neutral-900">
                                    <tr v-for="m in selectedTeam.membersWithRoles" :key="m.id"
                                        class="hover:bg-neutral-900/40 transition-colors">
                                        <!-- Member Name -->
                                        <td
                                            class="py-3.5 px-4 font-semibold text-neutral-100 flex items-center gap-2.5">
                                            <div
                                                class="w-7 h-7 rounded-lg bg-neutral-900 border border-neutral-800 text-cyan-400 flex items-center justify-center font-bold text-xs">
                                                {{ m.name.charAt(0).toUpperCase() }}
                                            </div>
                                            <span>{{ m.name }}</span>
                                        </td>

                                        <!-- Email -->
                                        <td class="py-3.5 px-4 font-mono text-neutral-400">
                                            {{ m.email }}
                                        </td>

                                        <!-- Role Badge -->
                                        <td class="py-3.5 px-4">
                                            <span v-if="m.role === 'creator'"
                                                class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-amber-950/80 border border-amber-800/60 text-amber-400">
                                                <Crown class="w-3 h-3 text-amber-400" />
                                                <span>Creator</span>
                                            </span>
                                            <span v-else-if="m.role === 'admin'"
                                                class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-indigo-950/80 border border-indigo-800/60 text-indigo-400">
                                                <Shield class="w-3 h-3 text-indigo-400" />
                                                <span>Admin</span>
                                            </span>
                                            <span v-else
                                                class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold uppercase bg-neutral-900 border border-neutral-800 text-neutral-400">
                                                <User class="w-3 h-3 text-neutral-400" />
                                                <span>User</span>
                                            </span>
                                        </td>

                                        <!-- Change Role Dropdown (Creator Only) -->
                                        <td class="py-3.5 px-4">
                                            <div v-if="currentUserRole === 'creator' && m.role !== 'creator'">
                                                <select :value="m.role"
                                                    @change="updateMemberRole(m.id, ($event.target as HTMLSelectElement).value)"
                                                    class="bg-neutral-900 text-neutral-200 border border-neutral-800 rounded px-2 py-1 text-xs focus:outline-none focus:border-cyan-500">
                                                    <option value="user">User</option>
                                                    <option value="admin">Admin</option>
                                                </select>
                                            </div>
                                            <span v-else class="text-[11px] text-neutral-500 italic">
                                                {{ m.role === 'creator' ? 'Creator (Owner)' : 'Fixed' }}
                                            </span>
                                        </td>

                                        <!-- Remove Action -->
                                        <td class="py-3.5 px-4 text-right">
                                            <!-- Creator can remove admins & users -->
                                            <!-- Admin can remove users only -->
                                            <!-- User cannot remove anyone -->
                                            <button v-if="
                                                m.role !== 'creator' &&
                                                String(m.id) !== String(authUser?.id) &&
                                                (currentUserRole === 'creator' || (currentUserRole === 'admin' && m.role === 'user'))
                                            " @click="removeMember(m.id, m.name)" title="Remove Member"
                                                class="px-2.5 py-1 text-[11px] font-semibold text-rose-400 hover:text-rose-300 bg-rose-950/60 hover:bg-rose-900 border border-rose-800/40 rounded-lg transition">
                                                Remove
                                            </button>
                                            <span v-else class="text-[11px] text-neutral-600 font-mono">
                                                {{ String(m.id) === String(authUser?.id) ? '(You)' : 'Protected' }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </template>
            </main>
        </div>

        <!-- NEW TEAM MODAL -->
        <div v-if="showNewTeamModal"
            class="fixed inset-0 bg-black/85 backdrop-blur-md flex items-center justify-center z-50 p-4">
            <div class="w-full max-w-md bg-black border border-neutral-800 rounded-2xl p-6 shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-neutral-800 pb-3">
                    <h3 class="font-bold text-base text-white">Create New Workspace</h3>
                    <button @click="showNewTeamModal = false" class="text-neutral-400 hover:text-white">
                        <X class="w-5 h-5" />
                    </button>
                </div>
                <div v-if="createTeamError"
                    class="p-3 rounded-lg bg-rose-950/60 border border-rose-800/50 text-rose-300 text-xs">
                    {{ createTeamError }}
                </div>
                <div>
                    <label class="block text-xs font-medium text-neutral-400 mb-1">Workspace Name</label>
                    <input v-model="newTeamName" type="text" placeholder="e.g. Core Infrastructure Team"
                        class="w-full bg-neutral-900 border border-neutral-800 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-cyan-500" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-neutral-400 mb-1">Description</label>
                    <textarea v-model="newTeamDesc" rows="3" placeholder="Optional description..."
                        class="w-full bg-neutral-900 border border-neutral-800 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-cyan-500 resize-none"></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button @click="showNewTeamModal = false"
                        class="px-4 py-2 rounded-lg bg-neutral-900 text-neutral-300 text-xs font-medium">Cancel</button>
                    <button @click="createTeam"
                        class="px-4 py-2 rounded-lg bg-cyan-500 hover:bg-cyan-400 text-black text-xs font-semibold">Create
                        Workspace</button>
                </div>
            </div>
        </div>

        <!-- EDIT TEAM MODAL -->
        <div v-if="showEditTeamModal"
            class="fixed inset-0 bg-black/85 backdrop-blur-md flex items-center justify-center z-50 p-4">
            <div class="w-full max-w-md bg-black border border-neutral-800 rounded-2xl p-6 shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-neutral-800 pb-3">
                    <h3 class="font-bold text-base text-white">Edit Workspace</h3>
                    <button @click="showEditTeamModal = false" class="text-neutral-400 hover:text-white">
                        <X class="w-5 h-5" />
                    </button>
                </div>
                <div v-if="editTeamError"
                    class="p-3 rounded-lg bg-rose-950/60 border border-rose-800/50 text-rose-300 text-xs">
                    {{ editTeamError }}
                </div>
                <div>
                    <label class="block text-xs font-medium text-neutral-400 mb-1">Workspace Name</label>
                    <input v-model="editTeamName" type="text" placeholder="e.g. Infrastructure Engineering"
                        class="w-full bg-neutral-900 border border-neutral-800 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-cyan-500" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-neutral-400 mb-1">Description</label>
                    <textarea v-model="editTeamDesc" rows="3" placeholder="Optional description..."
                        class="w-full bg-neutral-900 border border-neutral-800 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-cyan-500 resize-none"></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button @click="showEditTeamModal = false"
                        class="px-4 py-2 rounded-lg bg-neutral-900 text-neutral-300 text-xs font-medium">Cancel</button>
                    <button @click="updateTeam"
                        class="px-4 py-2 rounded-lg bg-cyan-500 hover:bg-cyan-400 text-black text-xs font-semibold">Save
                        Changes</button>
                </div>
            </div>
        </div>
    </div>
</template>
