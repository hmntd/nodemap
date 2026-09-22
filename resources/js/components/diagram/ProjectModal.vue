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
        <div
            v-if="showNewProjectModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/85 p-4 backdrop-blur-md"
        >
            <div
                class="w-full max-w-md space-y-4 rounded-2xl border border-neutral-800 bg-black p-6 shadow-2xl"
            >
                <div
                    class="flex items-center justify-between border-b border-neutral-800 pb-3"
                >
                    <h3 class="text-base font-bold text-white">
                        Create New Project
                    </h3>
                    <button
                        @click="emit('closeNewProject')"
                        class="text-neutral-400 hover:text-white"
                    >
                        <X class="h-5 w-5" />
                    </button>
                </div>
                <div
                    v-if="createProjectError"
                    class="rounded-lg border border-rose-800/50 bg-rose-950/60 p-3 text-xs text-rose-300"
                >
                    {{ createProjectError }}
                </div>
                <div>
                    <label
                        class="mb-1 block text-xs font-medium text-neutral-400"
                        >Project Title</label
                    >
                    <input
                        :value="newProjectTitle"
                        @input="
                            emit(
                                'update:newProjectTitle',
                                ($event.target as HTMLInputElement).value,
                            )
                        "
                        type="text"
                        placeholder="e.g. Payment Gateway Service"
                        class="w-full rounded-lg border border-neutral-800 bg-neutral-900 px-3 py-2 text-xs text-white focus:border-cyan-500 focus:outline-none"
                    />
                </div>
                <div>
                    <label
                        class="mb-1 block text-xs font-medium text-neutral-400"
                        >Description</label
                    >
                    <textarea
                        :value="newProjectDesc"
                        @input="
                            emit(
                                'update:newProjectDesc',
                                ($event.target as HTMLTextAreaElement).value,
                            )
                        "
                        rows="3"
                        placeholder="Optional project description..."
                        class="w-full resize-none rounded-lg border border-neutral-800 bg-neutral-900 px-3 py-2 text-xs text-white focus:border-cyan-500 focus:outline-none"
                    ></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button
                        @click="emit('closeNewProject')"
                        class="rounded-lg bg-neutral-900 px-4 py-2 text-xs font-medium text-neutral-300"
                    >
                        Cancel
                    </button>
                    <button
                        @click="emit('createProject')"
                        class="rounded-lg bg-cyan-500 px-4 py-2 text-xs font-semibold text-black hover:bg-cyan-400"
                    >
                        Create Project
                    </button>
                </div>
            </div>
        </div>

        <!-- SHARE PROJECT EXTERNALLY MODAL -->
        <div
            v-if="showShareProjectModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/85 p-4 backdrop-blur-md"
        >
            <div
                class="w-full max-w-lg space-y-4 rounded-2xl border border-neutral-800 bg-black p-6 shadow-2xl"
            >
                <div
                    class="flex items-center justify-between border-b border-neutral-800 pb-3"
                >
                    <div class="flex items-center gap-2">
                        <Share2 class="h-5 w-5 text-indigo-400" />
                        <h3 class="text-base font-bold text-white">
                            Share Project Externally -
                            {{ selectedProjectToShare?.title }}
                        </h3>
                    </div>
                    <button
                        @click="emit('closeShareProject')"
                        class="text-neutral-400 hover:text-white"
                    >
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <div
                    v-if="shareProjectError"
                    class="rounded-lg border border-rose-800/50 bg-rose-950/60 p-3 text-xs text-rose-300"
                >
                    {{ shareProjectError }}
                </div>
                <div
                    v-if="shareProjectSuccess"
                    class="rounded-lg border border-emerald-800/50 bg-emerald-950/60 p-3 text-xs text-emerald-300"
                >
                    {{ shareProjectSuccess }}
                </div>

                <!-- Invite External User Form -->
                <div class="space-y-2">
                    <label class="block text-xs font-medium text-neutral-400">
                        Grant 1 Project Access to External User (by Email)
                    </label>
                    <p class="text-[11px] text-neutral-500">
                        The invited user can access only this project. Other
                        projects in your team remain hidden.
                    </p>
                    <div class="flex gap-2 pt-1">
                        <input
                            :value="shareEmail"
                            @input="
                                emit(
                                    'update:shareEmail',
                                    ($event.target as HTMLInputElement).value,
                                )
                            "
                            type="email"
                            placeholder="external.architect@company.com"
                            class="flex-1 rounded-lg border border-neutral-800 bg-neutral-900 px-3 py-2 text-xs text-white focus:border-indigo-500 focus:outline-none"
                        />
                        <button
                            @click="emit('shareProject')"
                            class="shrink-0 rounded-lg bg-indigo-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-indigo-500"
                        >
                            Share Access
                        </button>
                    </div>
                </div>

                <!-- Currently Shared External Users -->
                <div class="space-y-3 border-t border-neutral-800 pt-4">
                    <span
                        class="block text-xs font-semibold tracking-wider text-neutral-400 uppercase"
                        >Shared External Users</span
                    >

                    <div
                        v-if="selectedProjectToShare?.sharedUsers?.length"
                        class="max-h-48 space-y-2 overflow-y-auto"
                    >
                        <div
                            v-for="u in selectedProjectToShare.sharedUsers"
                            :key="u.id"
                            class="border-neutral-850 flex items-center justify-between rounded-xl border bg-neutral-950 p-2.5"
                        >
                            <div>
                                <div
                                    class="text-xs font-semibold text-neutral-200"
                                >
                                    {{ u.name }}
                                </div>
                                <div
                                    class="font-mono text-[10px] text-neutral-500"
                                >
                                    {{ u.email }}
                                </div>
                            </div>
                            <button
                                @click="emit('revokeProjectAccess', u.id)"
                                title="Revoke Access"
                                class="rounded-lg border border-rose-800/40 bg-rose-950/60 px-2.5 py-1 text-xs font-semibold text-rose-400 transition hover:bg-rose-900 hover:text-rose-300"
                            >
                                Revoke
                            </button>
                        </div>
                    </div>
                    <div v-else class="text-xs text-neutral-500 italic">
                        Not shared with any external users yet.
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button
                        @click="emit('closeShareProject')"
                        class="rounded-lg bg-neutral-900 px-4 py-2 text-xs font-medium text-neutral-300"
                    >
                        Done
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
