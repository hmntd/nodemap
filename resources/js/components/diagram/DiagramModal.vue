<script setup lang="ts">
import { X } from '@lucide/vue';

const props = defineProps<{
    showNewDiagramModal: boolean;
    showEditDiagramModal: boolean;
    newDiagramTitle: string;
    newDiagramDesc: string;
    createDiagramError: string;
    editDiagramTitle: string;
    editDiagramDesc: string;
    editDiagramError: string;
}>();

const emit = defineEmits<{
    (e: 'update:newDiagramTitle', val: string): void;
    (e: 'update:newDiagramDesc', val: string): void;
    (e: 'update:editDiagramTitle', val: string): void;
    (e: 'update:editDiagramDesc', val: string): void;
    (e: 'closeNewDiagram'): void;
    (e: 'closeEditDiagram'): void;
    (e: 'createDiagram'): void;
    (e: 'updateDiagram'): void;
}>();
</script>

<template>
    <div>
        <!-- NEW DIAGRAM BOARD MODAL -->
        <div
            v-if="showNewDiagramModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/85 p-4 backdrop-blur-md"
        >
            <div
                class="w-full max-w-md space-y-4 rounded-2xl border border-neutral-800 bg-black p-6 shadow-2xl"
            >
                <div
                    class="flex items-center justify-between border-b border-neutral-800 pb-3"
                >
                    <h3 class="text-base font-bold text-white">
                        Create New Diagram Board
                    </h3>
                    <button
                        @click="emit('closeNewDiagram')"
                        class="text-neutral-400 hover:text-white"
                    >
                        <X class="h-5 w-5" />
                    </button>
                </div>
                <div
                    v-if="createDiagramError"
                    class="rounded-lg border border-rose-800/50 bg-rose-950/60 p-3 text-xs text-rose-300"
                >
                    {{ createDiagramError }}
                </div>
                <div>
                    <label
                        class="mb-1 block text-xs font-medium text-neutral-400"
                        >Board Title</label
                    >
                    <input
                        :value="newDiagramTitle"
                        @input="
                            emit(
                                'update:newDiagramTitle',
                                ($event.target as HTMLInputElement).value,
                            )
                        "
                        type="text"
                        placeholder="e.g. Core Topology Diagram"
                        class="w-full rounded-lg border border-neutral-800 bg-neutral-900 px-3 py-2 text-xs text-white focus:border-cyan-500 focus:outline-none"
                    />
                </div>
                <div>
                    <label
                        class="mb-1 block text-xs font-medium text-neutral-400"
                        >Description</label
                    >
                    <textarea
                        :value="newDiagramDesc"
                        @input="
                            emit(
                                'update:newDiagramDesc',
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
                        @click="emit('closeNewDiagram')"
                        class="rounded-lg bg-neutral-900 px-4 py-2 text-xs font-medium text-neutral-300"
                    >
                        Cancel
                    </button>
                    <button
                        @click="emit('createDiagram')"
                        class="rounded-lg bg-cyan-500 px-4 py-2 text-xs font-semibold text-black hover:bg-cyan-400"
                    >
                        Create Board
                    </button>
                </div>
            </div>
        </div>

        <!-- EDIT DIAGRAM BOARD MODAL (RENAME BOARD) -->
        <div
            v-if="showEditDiagramModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/85 p-4 backdrop-blur-md"
        >
            <div
                class="w-full max-w-md space-y-4 rounded-2xl border border-neutral-800 bg-black p-6 shadow-2xl"
            >
                <div
                    class="flex items-center justify-between border-b border-neutral-800 pb-3"
                >
                    <h3 class="text-base font-bold text-white">
                        Rename Diagram Board
                    </h3>
                    <button
                        @click="emit('closeEditDiagram')"
                        class="text-neutral-400 hover:text-white"
                    >
                        <X class="h-5 w-5" />
                    </button>
                </div>
                <div
                    v-if="editDiagramError"
                    class="rounded-lg border border-rose-800/50 bg-rose-950/60 p-3 text-xs text-rose-300"
                >
                    {{ editDiagramError }}
                </div>
                <div>
                    <label
                        class="mb-1 block text-xs font-medium text-neutral-400"
                        >Board Title</label
                    >
                    <input
                        :value="editDiagramTitle"
                        @input="
                            emit(
                                'update:editDiagramTitle',
                                ($event.target as HTMLInputElement).value,
                            )
                        "
                        type="text"
                        placeholder="e.g. Core Topology Diagram"
                        class="w-full rounded-lg border border-neutral-800 bg-neutral-900 px-3 py-2 text-xs text-white focus:border-cyan-500 focus:outline-none"
                    />
                </div>
                <div>
                    <label
                        class="mb-1 block text-xs font-medium text-neutral-400"
                        >Description</label
                    >
                    <textarea
                        :value="editDiagramDesc"
                        @input="
                            emit(
                                'update:editDiagramDesc',
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
                        @click="emit('closeEditDiagram')"
                        class="rounded-lg bg-neutral-900 px-4 py-2 text-xs font-medium text-neutral-300"
                    >
                        Cancel
                    </button>
                    <button
                        @click="emit('updateDiagram')"
                        class="rounded-lg bg-cyan-500 px-4 py-2 text-xs font-semibold text-black hover:bg-cyan-400"
                    >
                        Save Board Title
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
