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
                        Create New Team Workspace
                    </h3>
                    <button
                        @click="emit('closeNewTeam')"
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
                        >Team Workspace Name</label
                    >
                    <input
                        :value="newTeamName"
                        @input="
                            emit(
                                'update:newTeamName',
                                ($event.target as HTMLInputElement).value,
                            )
                        "
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
                        :value="newTeamDesc"
                        @input="
                            emit(
                                'update:newTeamDesc',
                                ($event.target as HTMLTextAreaElement).value,
                            )
                        "
                        rows="3"
                        placeholder="Optional description..."
                        class="w-full resize-none rounded-lg border border-neutral-800 bg-neutral-900 px-3 py-2 text-xs text-white focus:border-cyan-500 focus:outline-none"
                    ></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button
                        @click="emit('closeNewTeam')"
                        class="rounded-lg bg-neutral-900 px-4 py-2 text-xs font-medium text-neutral-300"
                    >
                        Cancel
                    </button>
                    <button
                        @click="emit('createTeam')"
                        class="rounded-lg bg-cyan-500 px-4 py-2 text-xs font-semibold text-black hover:bg-cyan-400"
                    >
                        Create Team Workspace
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
                        Rename Team Workspace
                    </h3>
                    <button
                        @click="emit('closeEditTeam')"
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
                        :value="editTeamName"
                        @input="
                            emit(
                                'update:editTeamName',
                                ($event.target as HTMLInputElement).value,
                            )
                        "
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
                        :value="editTeamDesc"
                        @input="
                            emit(
                                'update:editTeamDesc',
                                ($event.target as HTMLTextAreaElement).value,
                            )
                        "
                        rows="3"
                        placeholder="Optional description..."
                        class="w-full resize-none rounded-lg border border-neutral-800 bg-neutral-900 px-3 py-2 text-xs text-white focus:border-cyan-500 focus:outline-none"
                    ></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button
                        @click="emit('closeEditTeam')"
                        class="rounded-lg bg-neutral-900 px-4 py-2 text-xs font-medium text-neutral-300"
                    >
                        Cancel
                    </button>
                    <button
                        @click="emit('updateTeam')"
                        class="rounded-lg bg-cyan-500 px-4 py-2 text-xs font-semibold text-black hover:bg-cyan-400"
                    >
                        Save Changes
                    </button>
                </div>
            </div>
        </div>

        <!-- MANAGE TEAM MEMBERS MODAL -->
        <div
            v-if="showTeamMembersModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/85 p-4 backdrop-blur-md"
        >
            <div
                class="w-full max-w-lg space-y-4 rounded-2xl border border-neutral-800 bg-black p-6 shadow-2xl"
            >
                <div
                    class="flex items-center justify-between border-b border-neutral-800 pb-3"
                >
                    <div class="flex items-center gap-2">
                        <Users class="h-5 w-5 text-cyan-400" />
                        <h3 class="text-base font-bold text-white">
                            Team Members -
                            {{ currentActiveTeam?.name || 'Workspace' }}
                        </h3>
                    </div>
                    <button
                        @click="emit('closeTeamMembers')"
                        class="text-neutral-400 hover:text-white"
                    >
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <div
                    v-if="inviteMemberError"
                    class="rounded-lg border border-rose-800/50 bg-rose-950/60 p-3 text-xs text-rose-300"
                >
                    {{ inviteMemberError }}
                </div>
                <div
                    v-if="inviteMemberSuccess"
                    class="rounded-lg border border-emerald-800/50 bg-emerald-950/60 p-3 text-xs text-emerald-300"
                >
                    {{ inviteMemberSuccess }}
                </div>

                <!-- Invite Member Form -->
                <div class="space-y-2">
                    <label class="block text-xs font-medium text-neutral-400"
                        >Invite New Team Member (by Email)</label
                    >
                    <div class="flex gap-2">
                        <input
                            :value="inviteMemberEmail"
                            @input="
                                emit(
                                    'update:inviteMemberEmail',
                                    ($event.target as HTMLInputElement).value,
                                )
                            "
                            type="email"
                            placeholder="colleague@nodemap.dev"
                            class="flex-1 rounded-lg border border-neutral-800 bg-neutral-900 px-3 py-2 text-xs text-white focus:border-cyan-500 focus:outline-none"
                        />
                        <button
                            @click="emit('addTeamMember')"
                            class="shrink-0 rounded-lg bg-cyan-500 px-4 py-2 text-xs font-semibold text-black transition hover:bg-cyan-400"
                        >
                            Add to Team
                        </button>
                    </div>
                </div>

                <!-- Current Team Members List -->
                <div class="space-y-3 border-t border-neutral-800 pt-4">
                    <span
                        class="block text-xs font-semibold tracking-wider text-neutral-400 uppercase"
                        >Current Members</span
                    >

                    <!-- Team Owner -->
                    <div
                        v-if="currentActiveTeam?.owner"
                        class="border-neutral-850 flex items-center justify-between rounded-xl border bg-neutral-950 p-2.5"
                    >
                        <div class="flex items-center gap-2.5">
                            <div
                                class="flex h-7 w-7 items-center justify-center rounded-lg border border-amber-800/50 bg-amber-950/80 text-amber-400"
                            >
                                <Crown class="h-4 w-4" />
                            </div>
                            <div>
                                <div
                                    class="text-xs font-semibold text-neutral-200"
                                >
                                    {{ currentActiveTeam.owner.name }}
                                </div>
                                <div
                                    class="font-mono text-[10px] text-neutral-500"
                                >
                                    {{ currentActiveTeam.owner.email }}
                                </div>
                            </div>
                        </div>
                        <span
                            class="rounded border border-amber-800/40 bg-amber-950/50 px-2 py-0.5 text-[10px] font-bold text-amber-400 uppercase"
                        >
                            Team Owner
                        </span>
                    </div>

                    <!-- Members List -->
                    <div
                        v-if="currentActiveTeam?.members?.length"
                        class="max-h-48 space-y-2 overflow-y-auto"
                    >
                        <div
                            v-for="m in currentActiveTeam.members"
                            :key="m.id"
                            class="border-neutral-850 flex items-center justify-between rounded-xl border bg-neutral-950 p-2.5"
                        >
                            <div>
                                <div
                                    class="text-xs font-semibold text-neutral-200"
                                >
                                    {{ m.name }}
                                </div>
                                <div
                                    class="font-mono text-[10px] text-neutral-500"
                                >
                                    {{ m.email }}
                                </div>
                            </div>
                            <span
                                class="rounded border border-cyan-800/40 bg-cyan-950/50 px-2 py-0.5 text-[10px] font-medium text-cyan-400 uppercase"
                            >
                                Team Member
                            </span>
                        </div>
                    </div>
                    <div
                        v-else-if="!currentActiveTeam?.owner"
                        class="text-xs text-neutral-500 italic"
                    >
                        No members added yet.
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button
                        @click="emit('closeTeamMembers')"
                        class="rounded-lg bg-neutral-900 px-4 py-2 text-xs font-medium text-neutral-300"
                    >
                        Done
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
