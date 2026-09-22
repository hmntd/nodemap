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
    UPDATE_TEAM_MEMBER_ROLE,
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
    X,
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
const {
    result: teamsResult,
    loading: teamsLoading,
    refetch: refetchTeams,
} = useQuery(GET_TEAMS);
const { mutate: createTeamMut } = useMutation(CREATE_TEAM);
const { mutate: updateTeamMut } = useMutation(UPDATE_TEAM);
const { mutate: deleteTeamMut } = useMutation(DELETE_TEAM);

const { mutate: addTeamMemberMut } = useMutation(ADD_TEAM_MEMBER);
const { mutate: removeTeamMemberMut } = useMutation(REMOVE_TEAM_MEMBER);
const { mutate: updateTeamMemberRoleMut } = useMutation(
    UPDATE_TEAM_MEMBER_ROLE,
);

const teams = computed(() => teamsResult.value?.teams || []);

// Auto select first team
watch(
    teamsResult,
    (val) => {
        if (val?.teams?.length && !selectedTeamId.value) {
            selectedTeamId.value = String(val.teams[0].id);
        }
    },
    { immediate: true },
);

const selectedTeam = computed(() => {
    return (
        teams.value.find((t: any) => String(t.id) === selectedTeamId.value) ||
        null
    );
});

// Compute active user's role in selected team: 'creator' | 'admin' | 'user'
const currentUserRole = computed<'creator' | 'admin' | 'user'>(() => {
    if (!selectedTeam.value || !authUser.value) return 'user';

    if (String(selectedTeam.value.user_id) === String(authUser.value.id)) {
        return 'creator';
    }

    const member = (selectedTeam.value.membersWithRoles || []).find(
        (m: any) => String(m.id) === String(authUser.value?.id),
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
        const errMsg =
            e.graphQLErrors?.[0]?.message ||
            e.message ||
            'Failed to create workspace.';
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
        setTimeout(() => {
            actionSuccess.value = '';
        }, 3000);
    } catch (e: any) {
        console.error('Failed to update team:', e);
        const errMsg =
            e.graphQLErrors?.[0]?.message ||
            e.message ||
            'Failed to update workspace.';
        editTeamError.value = errMsg;
    }
}

async function deleteTeam() {
    if (!selectedTeamId.value || currentUserRole.value !== 'creator') return;

    if (
        !confirm(
            `Are you sure you want to delete workspace "${selectedTeam.value?.name}"? All associated projects will be removed.`,
        )
    )
        return;

    actionError.value = '';
    try {
        await deleteTeamMut({ id: selectedTeamId.value });
        selectedTeamId.value = '';
        await refetchTeams();
        actionSuccess.value = 'Workspace deleted successfully.';
        setTimeout(() => {
            actionSuccess.value = '';
        }, 3000);
    } catch (e: any) {
        console.error('Failed to delete team:', e);
        const errMsg =
            e.graphQLErrors?.[0]?.message ||
            e.message ||
            'Failed to delete workspace.';
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
        setTimeout(() => {
            inviteSuccess.value = '';
        }, 4000);
    } catch (e: any) {
        console.error('Failed to invite member:', e);
        const errMsg =
            e.graphQLErrors?.[0]?.message ||
            e.message ||
            'Failed to invite member.';
        inviteError.value = errMsg;
    }
}

async function removeMember(userId: string, memberName: string) {
    if (!selectedTeamId.value) return;

    if (
        !confirm(
            `Are you sure you want to remove ${memberName} from this workspace?`,
        )
    )
        return;

    actionError.value = '';
    actionSuccess.value = '';
    try {
        await removeTeamMemberMut({
            team_id: selectedTeamId.value,
            user_id: userId,
        });
        await refetchTeams();
        actionSuccess.value = `Removed ${memberName} from workspace.`;
        setTimeout(() => {
            actionSuccess.value = '';
        }, 3000);
    } catch (e: any) {
        console.error('Failed to remove member:', e);
        const errMsg =
            e.graphQLErrors?.[0]?.message ||
            e.message ||
            'Failed to remove member.';
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
        setTimeout(() => {
            actionSuccess.value = '';
        }, 3000);
    } catch (e: any) {
        console.error('Failed to update member role:', e);
        const errMsg =
            e.graphQLErrors?.[0]?.message ||
            e.message ||
            'Failed to update member role.';
        actionError.value = errMsg;
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
        <!-- TOP NAVIGATION BAR -->
        <header
            class="z-30 flex h-16 shrink-0 items-center justify-between border-b border-neutral-800 bg-black px-6"
        >
            <div class="flex items-center gap-6">
                <!-- Brand -->
                <Link href="/dashboard" class="group flex items-center gap-2.5">
                    <div
                        class="h-9 w-9 rounded-xl bg-gradient-to-tr from-cyan-500 via-indigo-500 to-purple-600 p-0.5 shadow-lg shadow-cyan-500/20 transition-transform group-hover:scale-105"
                    >
                        <div
                            class="flex h-full w-full items-center justify-center rounded-[10px] bg-black"
                        >
                            <Sparkles class="h-5 w-5 text-cyan-400" />
                        </div>
                    </div>
                    <div>
                        <h1
                            class="bg-gradient-to-r from-white via-cyan-200 to-indigo-300 bg-clip-text text-lg font-black tracking-tight text-transparent"
                        >
                            nodemap
                        </h1>
                        <p
                            class="font-mono text-[10px] tracking-widest text-neutral-500 uppercase"
                        >
                            Workspace Portal
                        </p>
                    </div>
                </Link>

                <!-- Navigation Tabs -->
                <div
                    class="flex items-center gap-2 rounded-xl border border-neutral-800 bg-neutral-950 p-1 text-xs font-medium"
                >
                    <Link
                        href="/dashboard"
                        class="flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-neutral-400 transition hover:bg-neutral-900 hover:text-white"
                    >
                        <LayoutGrid class="h-3.5 w-3.5" />
                        <span>Architect Canvas</span>
                    </Link>
                    <div
                        class="flex items-center gap-1.5 rounded-lg border border-neutral-800 bg-neutral-900 px-3 py-1.5 font-semibold text-cyan-400"
                    >
                        <Users class="h-3.5 w-3.5" />
                        <span>Workspace Management</span>
                    </div>
                </div>
            </div>

            <!-- Profile & Logout -->
            <div v-if="authUser" class="flex items-center gap-3">
                <div class="hidden text-right md:block">
                    <div class="text-xs font-semibold text-neutral-200">
                        {{ authUser.name }}
                    </div>
                    <div class="font-mono text-[10px] text-neutral-500">
                        {{ authUser.email }}
                    </div>
                </div>
                <button
                    @click="handleLogout"
                    title="Sign Out"
                    class="rounded-lg border border-neutral-800 bg-neutral-900 p-2 text-neutral-400 transition hover:bg-neutral-800 hover:text-rose-400"
                >
                    <LogOut class="h-4 w-4" />
                </button>
            </div>
        </header>

        <!-- MAIN LAYOUT -->
        <div class="flex flex-1 overflow-hidden">
            <!-- LEFT SIDEBAR: WORKSPACES SELECTOR LIST -->
            <aside
                class="flex w-80 shrink-0 flex-col border-r border-neutral-800 bg-black"
            >
                <div
                    class="flex items-center justify-between border-b border-neutral-800 p-4"
                >
                    <span
                        class="text-xs font-semibold tracking-wider text-neutral-400 uppercase"
                        >Your Workspaces</span
                    >
                    <button
                        @click="showNewTeamModal = true"
                        title="Create New Workspace"
                        class="flex items-center gap-1 rounded-lg bg-cyan-500 px-2.5 py-1 text-xs font-semibold text-black transition hover:bg-cyan-400"
                    >
                        <Plus class="h-3.5 w-3.5" />
                        <span>New</span>
                    </button>
                </div>

                <div
                    v-if="teamsLoading"
                    class="flex items-center justify-center gap-2 p-6 text-center text-xs text-neutral-500"
                >
                    <Loader2 class="h-4 w-4 animate-spin text-cyan-400" />
                    <span>Loading workspaces...</span>
                </div>

                <div v-else class="flex-1 space-y-2 overflow-y-auto p-3">
                    <button
                        v-for="t in teams"
                        :key="t.id"
                        @click="selectedTeamId = String(t.id)"
                        class="group relative w-full rounded-xl border p-3 text-left transition-all duration-200"
                        :class="[
                            String(t.id) === selectedTeamId
                                ? 'border-cyan-500/60 bg-neutral-900 text-white shadow-[0_0_15px_rgba(6,182,212,0.15)]'
                                : 'border-neutral-850 bg-black text-neutral-300 hover:border-neutral-700 hover:bg-neutral-900/60',
                        ]"
                    >
                        <div class="mb-1 flex items-center justify-between">
                            <h3
                                class="truncate text-xs font-bold transition-colors group-hover:text-cyan-300"
                            >
                                {{ t.name }}
                            </h3>

                            <!-- User Role Badge in this Workspace -->
                            <span
                                v-if="
                                    String(t.user_id) === String(authUser?.id)
                                "
                                class="flex items-center gap-1 rounded border border-amber-800/60 bg-amber-950/80 px-1.5 py-0.5 text-[9px] font-bold text-amber-400 uppercase"
                            >
                                <Crown class="h-2.5 w-2.5" />
                                <span>Creator</span>
                            </span>
                            <span
                                v-else-if="
                                    (t.membersWithRoles || []).some(
                                        (m: any) =>
                                            String(m.id) ===
                                                String(authUser?.id) &&
                                            m.role === 'admin',
                                    )
                                "
                                class="flex items-center gap-1 rounded border border-indigo-800/60 bg-indigo-950/80 px-1.5 py-0.5 text-[9px] font-bold text-indigo-400 uppercase"
                            >
                                <Shield class="h-2.5 w-2.5" />
                                <span>Admin</span>
                            </span>
                            <span
                                v-else
                                class="flex items-center gap-1 rounded border border-neutral-800 bg-neutral-900 px-1.5 py-0.5 text-[9px] font-semibold text-neutral-400 uppercase"
                            >
                                <User class="h-2.5 w-2.5" />
                                <span>User</span>
                            </span>
                        </div>

                        <p
                            v-if="t.description"
                            class="mb-2 truncate text-[11px] text-neutral-500"
                        >
                            {{ t.description }}
                        </p>

                        <div
                            class="flex items-center gap-3 text-[10px] text-neutral-400"
                        >
                            <span class="flex items-center gap-1">
                                <Users class="h-3 w-3 text-cyan-400" />
                                <span
                                    >{{
                                        (t.membersWithRoles || []).length || 1
                                    }}
                                    Members</span
                                >
                            </span>
                            <span class="flex items-center gap-1">
                                <LayoutGrid class="h-3 w-3 text-indigo-400" />
                                <span
                                    >{{
                                        (t.projects || []).length
                                    }}
                                    Projects</span
                                >
                            </span>
                        </div>
                    </button>
                </div>
            </aside>

            <!-- RIGHT WORKSPACE MANAGEMENT CONTENT PANEL -->
            <main class="flex-1 space-y-6 overflow-y-auto bg-black p-8">
                <!-- Action Status Banner -->
                <div
                    v-if="actionError"
                    class="flex items-center justify-between rounded-xl border border-rose-800/60 bg-rose-950/70 p-3.5 text-xs text-rose-300"
                >
                    <div class="flex items-center gap-2">
                        <AlertCircle class="h-4 w-4 shrink-0 text-rose-400" />
                        <span>{{ actionError }}</span>
                    </div>
                    <button
                        @click="actionError = ''"
                        class="text-rose-400 hover:text-white"
                    >
                        <X class="h-4 w-4" />
                    </button>
                </div>
                <div
                    v-if="actionSuccess"
                    class="flex items-center justify-between rounded-xl border border-emerald-800/60 bg-emerald-950/70 p-3.5 text-xs text-emerald-300"
                >
                    <div class="flex items-center gap-2">
                        <Check class="h-4 w-4 shrink-0 text-emerald-400" />
                        <span>{{ actionSuccess }}</span>
                    </div>
                    <button
                        @click="actionSuccess = ''"
                        class="text-emerald-400 hover:text-white"
                    >
                        <X class="h-4 w-4" />
                    </button>
                </div>

                <div
                    v-if="!selectedTeam"
                    class="flex h-full flex-col items-center justify-center text-neutral-500"
                >
                    <Users class="mb-3 h-12 w-12 text-neutral-700" />
                    <p class="text-sm">
                        Select a workspace from the left sidebar to manage team
                        members & roles.
                    </p>
                </div>

                <template v-else>
                    <!-- Workspace Header Banner -->
                    <div
                        class="flex items-start justify-between rounded-2xl border border-neutral-800 bg-neutral-950 p-6"
                    >
                        <div>
                            <div class="mb-1 flex items-center gap-3">
                                <h2
                                    class="text-xl font-extrabold tracking-tight text-white"
                                >
                                    {{ selectedTeam.name }}
                                </h2>
                                <!-- Current User's Role Pill -->
                                <span
                                    v-if="currentUserRole === 'creator'"
                                    class="flex items-center gap-1.5 rounded-full border border-amber-800/60 bg-amber-950/80 px-2.5 py-1 text-xs font-bold text-amber-400 uppercase"
                                >
                                    <Crown class="h-3.5 w-3.5 text-amber-400" />
                                    <span>Creator Role</span>
                                </span>
                                <span
                                    v-else-if="currentUserRole === 'admin'"
                                    class="flex items-center gap-1.5 rounded-full border border-indigo-800/60 bg-indigo-950/80 px-2.5 py-1 text-xs font-bold text-indigo-400 uppercase"
                                >
                                    <Shield
                                        class="h-3.5 w-3.5 text-indigo-400"
                                    />
                                    <span>Admin Role</span>
                                </span>
                                <span
                                    v-else
                                    class="flex items-center gap-1.5 rounded-full border border-neutral-800 bg-neutral-900 px-2.5 py-1 text-xs font-medium text-neutral-400 uppercase"
                                >
                                    <User
                                        class="h-3.5 w-3.5 text-neutral-400"
                                    />
                                    <span>Standard User Role</span>
                                </span>
                            </div>
                            <p class="max-w-xl text-xs text-neutral-400">
                                {{
                                    selectedTeam.description ||
                                    'No description provided for this team workspace.'
                                }}
                            </p>
                        </div>

                        <!-- Workspace Actions -->
                        <div class="flex items-center gap-2">
                            <button
                                v-if="
                                    currentUserRole === 'creator' ||
                                    currentUserRole === 'admin'
                                "
                                @click="openEditTeamModal"
                                class="hover:bg-neutral-850 flex items-center gap-1.5 rounded-lg border border-neutral-800 bg-neutral-900 px-3 py-1.5 text-xs font-medium text-neutral-300 transition"
                            >
                                <Pencil class="h-3.5 w-3.5 text-cyan-400" />
                                <span>Edit Name</span>
                            </button>
                            <button
                                v-if="currentUserRole === 'creator'"
                                @click="deleteTeam"
                                class="flex items-center gap-1.5 rounded-lg border border-rose-800/50 bg-rose-950/60 px-3 py-1.5 text-xs font-medium text-rose-300 transition hover:bg-rose-900"
                            >
                                <Trash2 class="h-3.5 w-3.5 text-rose-400" />
                                <span>Delete Workspace</span>
                            </button>
                        </div>
                    </div>

                    <!-- Role Authorization Info Banner -->
                    <div
                        v-if="currentUserRole === 'user'"
                        class="flex items-center gap-3 rounded-xl border border-cyan-800/40 bg-cyan-950/30 p-4 text-xs text-cyan-300"
                    >
                        <ShieldAlert class="h-5 w-5 shrink-0 text-cyan-400" />
                        <div>
                            <span class="block font-semibold text-cyan-200"
                                >View Only Access Mode</span
                            >
                            <span
                                >As a standard user in this workspace, you can
                                view team members but cannot invite users,
                                change roles, or remove members. Contact an
                                Admin or the Creator to make changes.</span
                            >
                        </div>
                    </div>

                    <!-- INVITE MEMBER CARD (Enabled for Creator & Admin) -->
                    <div
                        v-if="
                            currentUserRole === 'creator' ||
                            currentUserRole === 'admin'
                        "
                        class="space-y-4 rounded-2xl border border-neutral-800 bg-neutral-950 p-6"
                    >
                        <div
                            class="border-neutral-850 flex items-center gap-2 border-b pb-3"
                        >
                            <UserPlus class="h-4 w-4 text-cyan-400" />
                            <h3 class="text-sm font-bold text-neutral-100">
                                Invite New Workspace Member
                            </h3>
                        </div>

                        <div
                            v-if="inviteError"
                            class="rounded-lg border border-rose-800/50 bg-rose-950/60 p-3 text-xs text-rose-300"
                        >
                            {{ inviteError }}
                        </div>
                        <div
                            v-if="inviteSuccess"
                            class="rounded-lg border border-emerald-800/50 bg-emerald-950/60 p-3 text-xs text-emerald-300"
                        >
                            {{ inviteSuccess }}
                        </div>

                        <div class="flex flex-col items-end gap-3 md:flex-row">
                            <div class="w-full flex-1 space-y-1">
                                <label
                                    class="block text-xs font-medium text-neutral-400"
                                    >User Email Address</label
                                >
                                <input
                                    v-model="inviteEmail"
                                    type="email"
                                    placeholder="architect@nodemap.dev"
                                    class="w-full rounded-lg border border-neutral-800 bg-neutral-900 px-3 py-2 text-xs text-white focus:border-cyan-500 focus:outline-none"
                                />
                            </div>

                            <div
                                v-if="currentUserRole === 'creator'"
                                class="w-full space-y-1 md:w-48"
                            >
                                <label
                                    class="block text-xs font-medium text-neutral-400"
                                    >Assign Role</label
                                >
                                <select
                                    v-model="inviteRole"
                                    class="w-full rounded-lg border border-neutral-800 bg-neutral-900 px-3 py-2 text-xs text-white focus:border-cyan-500 focus:outline-none"
                                >
                                    <option value="user">
                                        User (Standard)
                                    </option>
                                    <option value="admin">
                                        Admin (Manager)
                                    </option>
                                </select>
                            </div>

                            <button
                                @click="inviteMember"
                                class="flex w-full items-center justify-center gap-2 rounded-lg bg-cyan-500 px-5 py-2 text-xs font-semibold text-black transition hover:bg-cyan-400 md:w-auto"
                            >
                                <UserPlus class="h-4 w-4" />
                                <span>Invite Member</span>
                            </button>
                        </div>
                    </div>

                    <!-- MEMBERS & ROLES TABLE -->
                    <div
                        class="overflow-hidden rounded-2xl border border-neutral-800 bg-neutral-950"
                    >
                        <div
                            class="flex items-center justify-between border-b border-neutral-800 p-4"
                        >
                            <div class="flex items-center gap-2">
                                <Users class="h-4 w-4 text-cyan-400" />
                                <h3 class="text-sm font-bold text-white">
                                    Workspace Members & Hierarchy
                                </h3>
                            </div>
                            <span class="font-mono text-xs text-neutral-500">
                                Total:
                                {{
                                    (selectedTeam.membersWithRoles || []).length
                                }}
                                Members
                            </span>
                        </div>

                        <div class="overflow-x-auto">
                            <table
                                class="w-full border-collapse text-left text-xs"
                            >
                                <thead>
                                    <tr
                                        class="border-neutral-850 border-b bg-neutral-900/50 text-[10px] font-semibold tracking-wider text-neutral-400 uppercase"
                                    >
                                        <th class="px-4 py-3">Member</th>
                                        <th class="px-4 py-3">Email</th>
                                        <th class="px-4 py-3">Current Role</th>
                                        <th class="px-4 py-3">
                                            Role Action (Creator Only)
                                        </th>
                                        <th class="px-4 py-3 text-right">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-neutral-900">
                                    <tr
                                        v-for="m in selectedTeam.membersWithRoles"
                                        :key="m.id"
                                        class="transition-colors hover:bg-neutral-900/40"
                                    >
                                        <!-- Member Name -->
                                        <td
                                            class="flex items-center gap-2.5 px-4 py-3.5 font-semibold text-neutral-100"
                                        >
                                            <div
                                                class="flex h-7 w-7 items-center justify-center rounded-lg border border-neutral-800 bg-neutral-900 text-xs font-bold text-cyan-400"
                                            >
                                                {{
                                                    m.name
                                                        .charAt(0)
                                                        .toUpperCase()
                                                }}
                                            </div>
                                            <span>{{ m.name }}</span>
                                        </td>

                                        <!-- Email -->
                                        <td
                                            class="px-4 py-3.5 font-mono text-neutral-400"
                                        >
                                            {{ m.email }}
                                        </td>

                                        <!-- Role Badge -->
                                        <td class="px-4 py-3.5">
                                            <span
                                                v-if="m.role === 'creator'"
                                                class="inline-flex items-center gap-1 rounded-full border border-amber-800/60 bg-amber-950/80 px-2.5 py-0.5 text-[10px] font-extrabold text-amber-400 uppercase"
                                            >
                                                <Crown
                                                    class="h-3 w-3 text-amber-400"
                                                />
                                                <span>Creator</span>
                                            </span>
                                            <span
                                                v-else-if="m.role === 'admin'"
                                                class="inline-flex items-center gap-1 rounded-full border border-indigo-800/60 bg-indigo-950/80 px-2.5 py-0.5 text-[10px] font-extrabold text-indigo-400 uppercase"
                                            >
                                                <Shield
                                                    class="h-3 w-3 text-indigo-400"
                                                />
                                                <span>Admin</span>
                                            </span>
                                            <span
                                                v-else
                                                class="inline-flex items-center gap-1 rounded-full border border-neutral-800 bg-neutral-900 px-2.5 py-0.5 text-[10px] font-semibold text-neutral-400 uppercase"
                                            >
                                                <User
                                                    class="h-3 w-3 text-neutral-400"
                                                />
                                                <span>User</span>
                                            </span>
                                        </td>

                                        <!-- Change Role Dropdown (Creator Only) -->
                                        <td class="px-4 py-3.5">
                                            <div
                                                v-if="
                                                    currentUserRole ===
                                                        'creator' &&
                                                    m.role !== 'creator'
                                                "
                                            >
                                                <select
                                                    :value="m.role"
                                                    @change="
                                                        updateMemberRole(
                                                            m.id,
                                                            (
                                                                $event.target as HTMLSelectElement
                                                            ).value,
                                                        )
                                                    "
                                                    class="rounded border border-neutral-800 bg-neutral-900 px-2 py-1 text-xs text-neutral-200 focus:border-cyan-500 focus:outline-none"
                                                >
                                                    <option value="user">
                                                        User
                                                    </option>
                                                    <option value="admin">
                                                        Admin
                                                    </option>
                                                </select>
                                            </div>
                                            <span
                                                v-else
                                                class="text-[11px] text-neutral-500 italic"
                                            >
                                                {{
                                                    m.role === 'creator'
                                                        ? 'Creator (Owner)'
                                                        : 'Fixed'
                                                }}
                                            </span>
                                        </td>

                                        <!-- Remove Action -->
                                        <td class="px-4 py-3.5 text-right">
                                            <!-- Creator can remove admins & users -->
                                            <!-- Admin can remove users only -->
                                            <!-- User cannot remove anyone -->
                                            <button
                                                v-if="
                                                    m.role !== 'creator' &&
                                                    String(m.id) !==
                                                        String(authUser?.id) &&
                                                    (currentUserRole ===
                                                        'creator' ||
                                                        (currentUserRole ===
                                                            'admin' &&
                                                            m.role === 'user'))
                                                "
                                                @click="
                                                    removeMember(m.id, m.name)
                                                "
                                                title="Remove Member"
                                                class="rounded-lg border border-rose-800/40 bg-rose-950/60 px-2.5 py-1 text-[11px] font-semibold text-rose-400 transition hover:bg-rose-900 hover:text-rose-300"
                                            >
                                                Remove
                                            </button>
                                            <span
                                                v-else
                                                class="font-mono text-[11px] text-neutral-600"
                                            >
                                                {{
                                                    String(m.id) ===
                                                    String(authUser?.id)
                                                        ? '(You)'
                                                        : 'Protected'
                                                }}
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
        <div
            v-if="showNewTeamModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/85 p-4 backdrop-blur-md"
        >
            <div
                class="w-full max-w-md space-y-4 rounded-2xl border border-neutral-800 bg-black p-6 shadow-2xl"
            >
                <div
                    class="flex items-center justify-between border-b border-neutral-800 pb-3"
                >
                    <h3 class="text-base font-bold text-white">
                        Create New Workspace
                    </h3>
                    <button
                        @click="showNewTeamModal = false"
                        class="text-neutral-400 hover:text-white"
                    >
                        <X class="h-5 w-5" />
                    </button>
                </div>
                <div
                    v-if="createTeamError"
                    class="rounded-lg border border-rose-800/50 bg-rose-950/60 p-3 text-xs text-rose-300"
                >
                    {{ createTeamError }}
                </div>
                <div>
                    <label
                        class="mb-1 block text-xs font-medium text-neutral-400"
                        >Workspace Name</label
                    >
                    <input
                        v-model="newTeamName"
                        type="text"
                        placeholder="e.g. Core Infrastructure Team"
                        class="w-full rounded-lg border border-neutral-800 bg-neutral-900 px-3 py-2 text-xs text-white focus:border-cyan-500 focus:outline-none"
                    />
                </div>
                <div>
                    <label
                        class="mb-1 block text-xs font-medium text-neutral-400"
                        >Description</label
                    >
                    <textarea
                        v-model="newTeamDesc"
                        rows="3"
                        placeholder="Optional description..."
                        class="w-full resize-none rounded-lg border border-neutral-800 bg-neutral-900 px-3 py-2 text-xs text-white focus:border-cyan-500 focus:outline-none"
                    ></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button
                        @click="showNewTeamModal = false"
                        class="rounded-lg bg-neutral-900 px-4 py-2 text-xs font-medium text-neutral-300"
                    >
                        Cancel
                    </button>
                    <button
                        @click="createTeam"
                        class="rounded-lg bg-cyan-500 px-4 py-2 text-xs font-semibold text-black hover:bg-cyan-400"
                    >
                        Create Workspace
                    </button>
                </div>
            </div>
        </div>

        <!-- EDIT TEAM MODAL -->
        <div
            v-if="showEditTeamModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/85 p-4 backdrop-blur-md"
        >
            <div
                class="w-full max-w-md space-y-4 rounded-2xl border border-neutral-800 bg-black p-6 shadow-2xl"
            >
                <div
                    class="flex items-center justify-between border-b border-neutral-800 pb-3"
                >
                    <h3 class="text-base font-bold text-white">
                        Edit Workspace
                    </h3>
                    <button
                        @click="showEditTeamModal = false"
                        class="text-neutral-400 hover:text-white"
                    >
                        <X class="h-5 w-5" />
                    </button>
                </div>
                <div
                    v-if="editTeamError"
                    class="rounded-lg border border-rose-800/50 bg-rose-950/60 p-3 text-xs text-rose-300"
                >
                    {{ editTeamError }}
                </div>
                <div>
                    <label
                        class="mb-1 block text-xs font-medium text-neutral-400"
                        >Workspace Name</label
                    >
                    <input
                        v-model="editTeamName"
                        type="text"
                        placeholder="e.g. Infrastructure Engineering"
                        class="w-full rounded-lg border border-neutral-800 bg-neutral-900 px-3 py-2 text-xs text-white focus:border-cyan-500 focus:outline-none"
                    />
                </div>
                <div>
                    <label
                        class="mb-1 block text-xs font-medium text-neutral-400"
                        >Description</label
                    >
                    <textarea
                        v-model="editTeamDesc"
                        rows="3"
                        placeholder="Optional description..."
                        class="w-full resize-none rounded-lg border border-neutral-800 bg-neutral-900 px-3 py-2 text-xs text-white focus:border-cyan-500 focus:outline-none"
                    ></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button
                        @click="showEditTeamModal = false"
                        class="rounded-lg bg-neutral-900 px-4 py-2 text-xs font-medium text-neutral-300"
                    >
                        Cancel
                    </button>
                    <button
                        @click="updateTeam"
                        class="rounded-lg bg-cyan-500 px-4 py-2 text-xs font-semibold text-black hover:bg-cyan-400"
                    >
                        Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
