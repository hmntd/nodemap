<script setup lang="ts">
import { Share2, X } from '@lucide/vue';

const props = defineProps<{
    showNewProjectModal: boolean;
    showShareProjectModal: boolean;
    selectedProjectToShare: any;
    newProjectTitle: string;
    newProjectDesc: string;
    createProjectError: string;
    shareEmail: string;
    shareProjectError: string;
    shareProjectSuccess: string;
}>();

const emit = defineEmits<{
    (e: 'update:newProjectTitle', val: string): void;
    (e: 'update:newProjectDesc', val: string): void;
    (e: 'update:shareEmail', val: string): void;
    (e: 'closeNewProject'): void;
    (e: 'closeShareProject'): void;
    (e: 'createProject'): void;
    (e: 'shareProject'): void;
    (e: 'revokeProjectAccess', userId: string): void;
}>();
</script>

<template>
    <div>
        <!-- NEW PROJECT MODAL -->
        <div v-if="showNewProjectModal"
            class="fixed inset-0 bg-black/85 backdrop-blur-md flex items-center justify-center z-50 p-4">
            <div class="w-full max-w-md bg-black border border-neutral-800 rounded-2xl p-6 shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-neutral-800 pb-3">
                    <h3 class="font-bold text-base text-white">Create New Project</h3>
                    <button @click="emit('closeNewProject')" class="text-neutral-400 hover:text-white">
                        <X class="w-5 h-5" />
                    </button>
                </div>
                <div v-if="createProjectError"
                    class="p-3 rounded-lg bg-rose-950/60 border border-rose-800/50 text-rose-300 text-xs">
                    {{ createProjectError }}
                </div>
                <div>
                    <label class="block text-xs font-medium text-neutral-400 mb-1">Project Title</label>
                    <input :value="newProjectTitle"
                        @input="emit('update:newProjectTitle', ($event.target as HTMLInputElement).value)" type="text"
                        placeholder="e.g. Payment Gateway Service"
                        class="w-full bg-neutral-900 border border-neutral-800 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-cyan-500" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-neutral-400 mb-1">Description</label>
                    <textarea :value="newProjectDesc"
                        @input="emit('update:newProjectDesc', ($event.target as HTMLTextAreaElement).value)" rows="3"
                        placeholder="Optional project description..."
                        class="w-full bg-neutral-900 border border-neutral-800 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-cyan-500 resize-none"></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button @click="emit('closeNewProject')"
                        class="px-4 py-2 rounded-lg bg-neutral-900 text-neutral-300 text-xs font-medium">Cancel</button>
                    <button @click="emit('createProject')"
                        class="px-4 py-2 rounded-lg bg-cyan-500 hover:bg-cyan-400 text-black text-xs font-semibold">Create
                        Project</button>
                </div>
            </div>
        </div>

        <!-- SHARE PROJECT EXTERNALLY MODAL -->
        <div v-if="showShareProjectModal"
            class="fixed inset-0 bg-black/85 backdrop-blur-md flex items-center justify-center z-50 p-4">
            <div class="w-full max-w-lg bg-black border border-neutral-800 rounded-2xl p-6 shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-neutral-800 pb-3">
                    <div class="flex items-center gap-2">
                        <Share2 class="w-5 h-5 text-indigo-400" />
                        <h3 class="font-bold text-base text-white">
                            Share Project Externally - {{ selectedProjectToShare?.title }}
                        </h3>
                    </div>
                    <button @click="emit('closeShareProject')" class="text-neutral-400 hover:text-white">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <div v-if="shareProjectError"
                    class="p-3 rounded-lg bg-rose-950/60 border border-rose-800/50 text-rose-300 text-xs">
                    {{ shareProjectError }}
                </div>
                <div v-if="shareProjectSuccess"
                    class="p-3 rounded-lg bg-emerald-950/60 border border-emerald-800/50 text-emerald-300 text-xs">
                    {{ shareProjectSuccess }}
                </div>

                <!-- Invite External User Form -->
                <div class="space-y-2">
                    <label class="block text-xs font-medium text-neutral-400">
                        Grant 1 Project Access to External User (by Email)
                    </label>
                    <p class="text-[11px] text-neutral-500">
                        The invited user can access only this project. Other projects in your team remain hidden.
                    </p>
                    <div class="flex gap-2 pt-1">
                        <input :value="shareEmail"
                            @input="emit('update:shareEmail', ($event.target as HTMLInputElement).value)" type="email"
                            placeholder="external.architect@company.com"
                            class="flex-1 bg-neutral-900 border border-neutral-800 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500" />
                        <button @click="emit('shareProject')"
                            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs rounded-lg transition shrink-0">
                            Share Access
                        </button>
                    </div>
                </div>

                <!-- Currently Shared External Users -->
                <div class="border-t border-neutral-800 pt-4 space-y-3">
                    <span class="text-xs font-semibold text-neutral-400 uppercase tracking-wider block">Shared External
                        Users</span>

                    <div v-if="selectedProjectToShare?.sharedUsers?.length" class="space-y-2 max-h-48 overflow-y-auto">
                        <div v-for="u in selectedProjectToShare.sharedUsers" :key="u.id"
                            class="flex items-center justify-between p-2.5 bg-neutral-950 border border-neutral-850 rounded-xl">
                            <div>
                                <div class="text-xs font-semibold text-neutral-200">{{ u.name }}</div>
                                <div class="text-[10px] text-neutral-500 font-mono">{{ u.email }}</div>
                            </div>
                            <button @click="emit('revokeProjectAccess', u.id)" title="Revoke Access"
                                class="px-2.5 py-1 text-xs font-semibold text-rose-400 hover:text-rose-300 bg-rose-950/60 hover:bg-rose-900 border border-rose-800/40 rounded-lg transition">
                                Revoke
                            </button>
                        </div>
                    </div>
                    <div v-else class="text-xs text-neutral-500 italic">
                        Not shared with any external users yet.
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button @click="emit('closeShareProject')"
                        class="px-4 py-2 rounded-lg bg-neutral-900 text-neutral-300 text-xs font-medium">Done</button>
                </div>
            </div>
        </div>
    </div>
</template>
