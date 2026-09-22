<script setup lang="ts">
import { Users, Crown, X } from '@lucide/vue';

const props = defineProps<{
    showNewTeamModal: boolean;
    showEditTeamModal: boolean;
    showTeamMembersModal: boolean;
    currentActiveTeam: any;
    newTeamName: string;
    newTeamDesc: string;
    createTeamError: string;
    editTeamName: string;
    editTeamDesc: string;
    editTeamError: string;
    inviteMemberEmail: string;
    inviteMemberError: string;
    inviteMemberSuccess: string;
}>();

const emit = defineEmits<{
    (e: 'update:newTeamName', val: string): void;
    (e: 'update:newTeamDesc', val: string): void;
    (e: 'update:editTeamName', val: string): void;
    (e: 'update:editTeamDesc', val: string): void;
    (e: 'update:inviteMemberEmail', val: string): void;
    (e: 'closeNewTeam'): void;
    (e: 'closeEditTeam'): void;
    (e: 'closeTeamMembers'): void;
    (e: 'createTeam'): void;
    (e: 'updateTeam'): void;
    (e: 'addTeamMember'): void;
}>();
</script>

<template>
    <div>
        <!-- NEW TEAM MODAL -->
        <div v-if="showNewTeamModal"
            class="fixed inset-0 bg-black/85 backdrop-blur-md flex items-center justify-center z-50 p-4">
            <div class="w-full max-w-md bg-black border border-neutral-800 rounded-2xl p-6 shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-neutral-800 pb-3">
                    <h3 class="font-bold text-base text-white">Create New Team Workspace</h3>
                    <button @click="emit('closeNewTeam')" class="text-neutral-400 hover:text-white">
                        <X class="w-5 h-5" />
                    </button>
                </div>
                <div v-if="createTeamError"
                    class="p-3 rounded-lg bg-rose-950/60 border border-rose-800/50 text-rose-300 text-xs">
                    {{ createTeamError }}
                </div>
                <div>
                    <label class="block text-xs font-medium text-neutral-400 mb-1">Team Workspace Name</label>
                    <input :value="newTeamName"
                        @input="emit('update:newTeamName', ($event.target as HTMLInputElement).value)" type="text"
                        placeholder="e.g. Core Infrastructure Team"
                        class="w-full bg-neutral-900 border border-neutral-800 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-cyan-500" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-neutral-400 mb-1">Description</label>
                    <textarea :value="newTeamDesc"
                        @input="emit('update:newTeamDesc', ($event.target as HTMLTextAreaElement).value)" rows="3"
                        placeholder="Optional description..."
                        class="w-full bg-neutral-900 border border-neutral-800 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-cyan-500 resize-none"></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button @click="emit('closeNewTeam')"
                        class="px-4 py-2 rounded-lg bg-neutral-900 text-neutral-300 text-xs font-medium">Cancel</button>
                    <button @click="emit('createTeam')"
                        class="px-4 py-2 rounded-lg bg-cyan-500 hover:bg-cyan-400 text-black text-xs font-semibold">Create
                        Team Workspace</button>
                </div>
            </div>
        </div>

        <!-- EDIT TEAM MODAL -->
        <div v-if="showEditTeamModal"
            class="fixed inset-0 bg-black/85 backdrop-blur-md flex items-center justify-center z-50 p-4">
            <div class="w-full max-w-md bg-black border border-neutral-800 rounded-2xl p-6 shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-neutral-800 pb-3">
                    <h3 class="font-bold text-base text-white">Rename Team Workspace</h3>
                    <button @click="emit('closeEditTeam')" class="text-neutral-400 hover:text-white">
                        <X class="w-5 h-5" />
                    </button>
                </div>
                <div v-if="editTeamError"
                    class="p-3 rounded-lg bg-rose-950/60 border border-rose-800/50 text-rose-300 text-xs">
                    {{ editTeamError }}
                </div>
                <div>
                    <label class="block text-xs font-medium text-neutral-400 mb-1">Workspace Name</label>
                    <input :value="editTeamName"
                        @input="emit('update:editTeamName', ($event.target as HTMLInputElement).value)" type="text"
                        placeholder="e.g. Infrastructure Engineering"
                        class="w-full bg-neutral-900 border border-neutral-800 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-cyan-500" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-neutral-400 mb-1">Description</label>
                    <textarea :value="editTeamDesc"
                        @input="emit('update:editTeamDesc', ($event.target as HTMLTextAreaElement).value)" rows="3"
                        placeholder="Optional description..."
                        class="w-full bg-neutral-900 border border-neutral-800 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-cyan-500 resize-none"></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button @click="emit('closeEditTeam')"
                        class="px-4 py-2 rounded-lg bg-neutral-900 text-neutral-300 text-xs font-medium">Cancel</button>
                    <button @click="emit('updateTeam')"
                        class="px-4 py-2 rounded-lg bg-cyan-500 hover:bg-cyan-400 text-black text-xs font-semibold">Save
                        Changes</button>
                </div>
            </div>
        </div>

        <!-- MANAGE TEAM MEMBERS MODAL -->
        <div v-if="showTeamMembersModal"
            class="fixed inset-0 bg-black/85 backdrop-blur-md flex items-center justify-center z-50 p-4">
            <div class="w-full max-w-lg bg-black border border-neutral-800 rounded-2xl p-6 shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-neutral-800 pb-3">
                    <div class="flex items-center gap-2">
                        <Users class="w-5 h-5 text-cyan-400" />
                        <h3 class="font-bold text-base text-white">
                            Team Members - {{ currentActiveTeam?.name || 'Workspace' }}
                        </h3>
                    </div>
                    <button @click="emit('closeTeamMembers')" class="text-neutral-400 hover:text-white">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <div v-if="inviteMemberError"
                    class="p-3 rounded-lg bg-rose-950/60 border border-rose-800/50 text-rose-300 text-xs">
                    {{ inviteMemberError }}
                </div>
                <div v-if="inviteMemberSuccess"
                    class="p-3 rounded-lg bg-emerald-950/60 border border-emerald-800/50 text-emerald-300 text-xs">
                    {{ inviteMemberSuccess }}
                </div>

                <!-- Invite Member Form -->
                <div class="space-y-2">
                    <label class="block text-xs font-medium text-neutral-400">Invite New Team Member (by Email)</label>
                    <div class="flex gap-2">
                        <input :value="inviteMemberEmail"
                            @input="emit('update:inviteMemberEmail', ($event.target as HTMLInputElement).value)"
                            type="email" placeholder="colleague@nodemap.dev"
                            class="flex-1 bg-neutral-900 border border-neutral-800 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-cyan-500" />
                        <button @click="emit('addTeamMember')"
                            class="px-4 py-2 bg-cyan-500 hover:bg-cyan-400 text-black font-semibold text-xs rounded-lg transition shrink-0">
                            Add to Team
                        </button>
                    </div>
                </div>

                <!-- Current Team Members List -->
                <div class="border-t border-neutral-800 pt-4 space-y-3">
                    <span class="text-xs font-semibold text-neutral-400 uppercase tracking-wider block">Current
                        Members</span>

                    <!-- Team Owner -->
                    <div v-if="currentActiveTeam?.owner"
                        class="flex items-center justify-between p-2.5 bg-neutral-950 border border-neutral-850 rounded-xl">
                        <div class="flex items-center gap-2.5">
                            <div
                                class="w-7 h-7 rounded-lg bg-amber-950/80 border border-amber-800/50 text-amber-400 flex items-center justify-center">
                                <Crown class="w-4 h-4" />
                            </div>
                            <div>
                                <div class="text-xs font-semibold text-neutral-200">{{ currentActiveTeam.owner.name }}
                                </div>
                                <div class="text-[10px] text-neutral-500 font-mono">{{ currentActiveTeam.owner.email }}
                                </div>
                            </div>
                        </div>
                        <span
                            class="text-[10px] uppercase font-bold text-amber-400 px-2 py-0.5 rounded bg-amber-950/50 border border-amber-800/40">
                            Team Owner
                        </span>
                    </div>

                    <!-- Members List -->
                    <div v-if="currentActiveTeam?.members?.length" class="space-y-2 max-h-48 overflow-y-auto">
                        <div v-for="m in currentActiveTeam.members" :key="m.id"
                            class="flex items-center justify-between p-2.5 bg-neutral-950 border border-neutral-850 rounded-xl">
                            <div>
                                <div class="text-xs font-semibold text-neutral-200">{{ m.name }}</div>
                                <div class="text-[10px] text-neutral-500 font-mono">{{ m.email }}</div>
                            </div>
                            <span
                                class="text-[10px] uppercase font-medium text-cyan-400 px-2 py-0.5 rounded bg-cyan-950/50 border border-cyan-800/40">
                                Team Member
                            </span>
                        </div>
                    </div>
                    <div v-else-if="!currentActiveTeam?.owner" class="text-xs text-neutral-500 italic">
                        No members added yet.
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button @click="emit('closeTeamMembers')"
                        class="px-4 py-2 rounded-lg bg-neutral-900 text-neutral-300 text-xs font-medium">Done</button>
                </div>
            </div>
        </div>
    </div>
</template>
