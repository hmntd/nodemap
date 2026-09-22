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
    LogOut
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
    <header class="h-16 border-b border-neutral-800 bg-black px-5 flex items-center justify-between z-30 shrink-0">
        <!-- Brand & Team / Diagram Picker -->
        <div class="flex items-center gap-3 min-w-0 flex-1">
            <div class="flex items-center gap-2.5 shrink-0">
                <div
                    class="w-9 h-9 rounded-xl bg-gradient-to-tr from-cyan-500 via-indigo-500 to-purple-600 p-0.5 shadow-lg shadow-cyan-500/20">
                    <div class="w-full h-full bg-black rounded-[10px] flex items-center justify-center">
                        <Sparkles class="w-5 h-5 text-cyan-400" />
                    </div>
                </div>
                <div class="hidden sm:block">
                    <h1
                        class="font-black text-lg tracking-tight bg-gradient-to-r from-white via-cyan-200 to-indigo-300 bg-clip-text text-transparent">
                        nodemap
                    </h1>
                    <p class="text-[10px] text-neutral-500 font-mono tracking-widest uppercase">Diagramming Platform</p>
                </div>
            </div>

            <!-- Sleek Pitch-Black Workspace Custom Dropdown -->
            <div
                class="flex items-center gap-1.5 bg-neutral-950 border border-neutral-800 rounded-xl p-1.5 flex-1 min-w-0 max-w-xs md:max-w-sm">
                <Users class="w-3.5 h-3.5 text-cyan-400 ml-1 shrink-0" />
                <CustomDropdown :model-value="activeTeamId"
                    @update:model-value="val => emit('update:activeTeamId', String(val))" :options="teamOptions"
                    placeholder="Select Workspace..." />
                <button @click="emit('openEditTeam')" title="Rename Workspace"
                    class="p-1.5 rounded-lg text-neutral-400 hover:text-cyan-400 hover:bg-neutral-900 border border-transparent hover:border-neutral-800 transition shrink-0">
                    <Pencil class="w-3.5 h-3.5" />
                </button>
                <button @click="emit('openTeamMembers')" title="Manage Team Members"
                    class="p-1.5 rounded-lg text-neutral-400 hover:text-cyan-400 hover:bg-neutral-900 border border-transparent hover:border-neutral-800 transition shrink-0">
                    <UserPlus class="w-3.5 h-3.5" />
                </button>
                <button @click="emit('openNewTeam')" title="Create New Team Workspace"
                    class="p-1.5 rounded-lg text-neutral-400 hover:text-white hover:bg-neutral-900 border border-transparent hover:border-neutral-800 transition shrink-0">
                    <Plus class="w-3.5 h-3.5" />
                </button>
            </div>

            <!-- Sleek Pitch-Black Board Custom Dropdown & Rename Board Action -->
            <div v-if="boardOptions.length"
                class="flex items-center gap-1.5 bg-neutral-950 border border-neutral-800 rounded-xl p-1.5 flex-1 min-w-0 max-w-xs md:max-w-md">
                <LayoutGrid class="w-3.5 h-3.5 text-indigo-400 ml-1 shrink-0" />
                <CustomDropdown :model-value="activeDiagramId"
                    @update:model-value="val => emit('update:activeDiagramId', String(val))" :options="boardOptions"
                    placeholder="Select Board..." />
                <button @click="emit('openEditDiagram')" title="Rename Board"
                    class="p-1.5 rounded-lg text-neutral-400 hover:text-cyan-400 hover:bg-neutral-900 border border-transparent hover:border-neutral-800 transition shrink-0">
                    <Pencil class="w-3.5 h-3.5" />
                </button>
            </div>
        </div>

        <!-- Toolbar Actions & Profile -->
        <div class="flex items-center gap-3">
            <!-- Status Badge -->
            <div
                class="flex items-center gap-2 text-xs px-3 py-1.5 rounded-full bg-neutral-950 border border-neutral-800">
                <Loader2 v-if="isSaving" class="w-3.5 h-3.5 text-cyan-400 animate-spin" />
                <span v-else class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span class="text-neutral-300 text-[11px] font-mono">{{ saveMessage }}</span>
            </div>

            <!-- Workspaces Portal Link -->
            <Link href="/workspaces"
                class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-neutral-950 hover:bg-neutral-900 border border-neutral-800 text-neutral-300 font-medium text-xs transition"
                title="Manage Workspaces & Roles">
                <Users class="w-4 h-4 text-cyan-400" />
                <span>Workspaces</span>
            </Link>

            <!-- Export Code Button -->
            <button @click="emit('openExport')"
                class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-indigo-950/60 hover:bg-indigo-900/80 text-indigo-300 border border-indigo-800/50 font-medium text-xs transition">
                <FileCode class="w-4 h-4 text-indigo-400" />
                <span>Export</span>
            </button>

            <!-- New Project Button -->
            <button @click="emit('openNewProject')"
                class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-cyan-500 hover:bg-cyan-400 text-black font-semibold text-xs shadow-lg shadow-cyan-500/20 transition">
                <Plus class="w-4 h-4" />
                <span>New Project</span>
            </button>

            <!-- User Profile & Logout -->
            <div v-if="authUser" class="flex items-center gap-2 border-l border-neutral-800 pl-3">
                <div class="text-right hidden md:block">
                    <div class="text-xs font-semibold text-neutral-200">{{ authUser.name }}</div>
                    <div class="text-[10px] text-neutral-500 font-mono">{{ authUser.email }}</div>
                </div>
                <button @click="emit('logout')" title="Sign Out"
                    class="p-2 rounded-lg bg-neutral-900 hover:bg-neutral-800 text-neutral-400 hover:text-rose-400 border border-neutral-800 transition">
                    <LogOut class="w-4 h-4" />
                </button>
            </div>
        </div>
    </header>
</template>
