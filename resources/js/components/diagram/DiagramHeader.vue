<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import CustomDropdown from '@/components/ui/CustomDropdown.vue';
import {
    Sparkles,
    Users,
    Pencil,
    UserPlus,
    Plus,
    LayoutGrid,
    Loader2,
    FileCode,
    LogOut,
} from '@lucide/vue';

const props = defineProps<{
    activeTeamId: string;
    activeDiagramId: string;
    teamOptions: Array<any>;
    boardOptions: Array<any>;
    authUser: any;
    isSaving: boolean;
    saveMessage: string;
}>();

const emit = defineEmits<{
    (e: 'update:activeTeamId', val: string): void;
    (e: 'update:activeDiagramId', val: string): void;
    (e: 'openEditTeam'): void;
    (e: 'openTeamMembers'): void;
    (e: 'openNewTeam'): void;
    (e: 'openEditDiagram'): void;
    (e: 'openNewProject'): void;
    (e: 'openExport'): void;
    (e: 'logout'): void;
}>();
</script>

<template>
    <header
        class="z-30 flex h-16 shrink-0 items-center justify-between border-b border-neutral-800 bg-black px-5"
    >
        <!-- Brand & Team / Diagram Picker -->
        <div class="flex min-w-0 flex-1 items-center gap-3">
            <div class="flex shrink-0 items-center gap-2.5">
                <div
                    class="h-9 w-9 rounded-xl bg-gradient-to-tr from-cyan-500 via-indigo-500 to-purple-600 p-0.5 shadow-lg shadow-cyan-500/20"
                >
                    <div
                        class="flex h-full w-full items-center justify-center rounded-[10px] bg-black"
                    >
                        <Sparkles class="h-5 w-5 text-cyan-400" />
                    </div>
                </div>
                <div class="hidden sm:block">
                    <h1
                        class="bg-gradient-to-r from-white via-cyan-200 to-indigo-300 bg-clip-text text-lg font-black tracking-tight text-transparent"
                    >
                        nodemap
                    </h1>
                    <p
                        class="font-mono text-[10px] tracking-widest text-neutral-500 uppercase"
                    >
                        Diagramming Platform
                    </p>
                </div>
            </div>

            <!-- Sleek Pitch-Black Workspace Custom Dropdown -->
            <div
                class="flex max-w-xs min-w-0 flex-1 items-center gap-1.5 rounded-xl border border-neutral-800 bg-neutral-950 p-1.5 md:max-w-sm"
            >
                <Users class="ml-1 h-3.5 w-3.5 shrink-0 text-cyan-400" />
                <CustomDropdown
                    :model-value="activeTeamId"
                    @update:model-value="
                        (val) => emit('update:activeTeamId', String(val))
                    "
                    :options="teamOptions"
                    placeholder="Select Workspace..."
                />
                <button
                    @click="emit('openEditTeam')"
                    title="Rename Workspace"
                    class="shrink-0 rounded-lg border border-transparent p-1.5 text-neutral-400 transition hover:border-neutral-800 hover:bg-neutral-900 hover:text-cyan-400"
                >
                    <Pencil class="h-3.5 w-3.5" />
                </button>
                <button
                    @click="emit('openTeamMembers')"
                    title="Manage Team Members"
                    class="shrink-0 rounded-lg border border-transparent p-1.5 text-neutral-400 transition hover:border-neutral-800 hover:bg-neutral-900 hover:text-cyan-400"
                >
                    <UserPlus class="h-3.5 w-3.5" />
                </button>
                <button
                    @click="emit('openNewTeam')"
                    title="Create New Team Workspace"
                    class="shrink-0 rounded-lg border border-transparent p-1.5 text-neutral-400 transition hover:border-neutral-800 hover:bg-neutral-900 hover:text-white"
                >
                    <Plus class="h-3.5 w-3.5" />
                </button>
            </div>

            <!-- Sleek Pitch-Black Board Custom Dropdown & Rename Board Action -->
            <div
                v-if="boardOptions.length"
                class="flex max-w-xs min-w-0 flex-1 items-center gap-1.5 rounded-xl border border-neutral-800 bg-neutral-950 p-1.5 md:max-w-md"
            >
                <LayoutGrid class="ml-1 h-3.5 w-3.5 shrink-0 text-indigo-400" />
                <CustomDropdown
                    :model-value="activeDiagramId"
                    @update:model-value="
                        (val) => emit('update:activeDiagramId', String(val))
                    "
                    :options="boardOptions"
                    placeholder="Select Board..."
                />
                <button
                    @click="emit('openEditDiagram')"
                    title="Rename Board"
                    class="shrink-0 rounded-lg border border-transparent p-1.5 text-neutral-400 transition hover:border-neutral-800 hover:bg-neutral-900 hover:text-cyan-400"
                >
                    <Pencil class="h-3.5 w-3.5" />
                </button>
            </div>
        </div>

        <!-- Toolbar Actions & Profile -->
        <div class="flex items-center gap-3">
            <!-- Status Badge -->
            <div
                class="flex items-center gap-2 rounded-full border border-neutral-800 bg-neutral-950 px-3 py-1.5 text-xs"
            >
                <Loader2
                    v-if="isSaving"
                    class="h-3.5 w-3.5 animate-spin text-cyan-400"
                />
                <span
                    v-else
                    class="h-2 w-2 animate-pulse rounded-full bg-emerald-400"
                ></span>
                <span class="font-mono text-[11px] text-neutral-300">{{
                    saveMessage
                }}</span>
            </div>

            <!-- Workspaces Portal Link -->
            <Link
                href="/workspaces"
                class="flex items-center gap-1.5 rounded-lg border border-neutral-800 bg-neutral-950 px-3 py-1.5 text-xs font-medium text-neutral-300 transition hover:bg-neutral-900"
                title="Manage Workspaces & Roles"
            >
                <Users class="h-4 w-4 text-cyan-400" />
                <span>Workspaces</span>
            </Link>

            <!-- Export Code Button -->
            <button
                @click="emit('openExport')"
                class="flex items-center gap-2 rounded-lg border border-indigo-800/50 bg-indigo-950/60 px-3 py-1.5 text-xs font-medium text-indigo-300 transition hover:bg-indigo-900/80"
            >
                <FileCode class="h-4 w-4 text-indigo-400" />
                <span>Export</span>
            </button>

            <!-- New Project Button -->
            <button
                @click="emit('openNewProject')"
                class="flex items-center gap-1.5 rounded-lg bg-cyan-500 px-3 py-1.5 text-xs font-semibold text-black shadow-lg shadow-cyan-500/20 transition hover:bg-cyan-400"
            >
                <Plus class="h-4 w-4" />
                <span>New Project</span>
            </button>

            <!-- User Profile & Logout -->
            <div
                v-if="authUser"
                class="flex items-center gap-2 border-l border-neutral-800 pl-3"
            >
                <div class="hidden text-right md:block">
                    <div class="text-xs font-semibold text-neutral-200">
                        {{ authUser.name }}
                    </div>
                    <div class="font-mono text-[10px] text-neutral-500">
                        {{ authUser.email }}
                    </div>
                </div>
                <button
                    @click="emit('logout')"
                    title="Sign Out"
                    class="rounded-lg border border-neutral-800 bg-neutral-900 p-2 text-neutral-400 transition hover:bg-neutral-800 hover:text-rose-400"
                >
                    <LogOut class="h-4 w-4" />
                </button>
            </div>
        </div>
    </header>
</template>
