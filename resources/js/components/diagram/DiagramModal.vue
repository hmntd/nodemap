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
        <div v-if="showNewDiagramModal"
            class="fixed inset-0 bg-black/85 backdrop-blur-md flex items-center justify-center z-50 p-4">
            <div class="w-full max-w-md bg-black border border-neutral-800 rounded-2xl p-6 shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-neutral-800 pb-3">
                    <h3 class="font-bold text-base text-white">Create New Diagram Board</h3>
                    <button @click="emit('closeNewDiagram')" class="text-neutral-400 hover:text-white">
                        <X class="w-5 h-5" />
                    </button>
                </div>
                <div v-if="createDiagramError"
                    class="p-3 rounded-lg bg-rose-950/60 border border-rose-800/50 text-rose-300 text-xs">
                    {{ createDiagramError }}
                </div>
                <div>
                    <label class="block text-xs font-medium text-neutral-400 mb-1">Board Title</label>
                    <input :value="newDiagramTitle"
                        @input="emit('update:newDiagramTitle', ($event.target as HTMLInputElement).value)" type="text"
                        placeholder="e.g. Core Topology Diagram"
                        class="w-full bg-neutral-900 border border-neutral-800 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-cyan-500" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-neutral-400 mb-1">Description</label>
                    <textarea :value="newDiagramDesc"
                        @input="emit('update:newDiagramDesc', ($event.target as HTMLTextAreaElement).value)" rows="3"
                        placeholder="Optional description..."
                        class="w-full bg-neutral-900 border border-neutral-800 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-cyan-500 resize-none"></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button @click="emit('closeNewDiagram')"
                        class="px-4 py-2 rounded-lg bg-neutral-900 text-neutral-300 text-xs font-medium">Cancel</button>
                    <button @click="emit('createDiagram')"
                        class="px-4 py-2 rounded-lg bg-cyan-500 hover:bg-cyan-400 text-black text-xs font-semibold">Create
                        Board</button>
                </div>
            </div>
        </div>

        <!-- EDIT DIAGRAM BOARD MODAL (RENAME BOARD) -->
        <div v-if="showEditDiagramModal"
            class="fixed inset-0 bg-black/85 backdrop-blur-md flex items-center justify-center z-50 p-4">
            <div class="w-full max-w-md bg-black border border-neutral-800 rounded-2xl p-6 shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-neutral-800 pb-3">
                    <h3 class="font-bold text-base text-white">Rename Diagram Board</h3>
                    <button @click="emit('closeEditDiagram')" class="text-neutral-400 hover:text-white">
                        <X class="w-5 h-5" />
                    </button>
                </div>
                <div v-if="editDiagramError"
                    class="p-3 rounded-lg bg-rose-950/60 border border-rose-800/50 text-rose-300 text-xs">
                    {{ editDiagramError }}
                </div>
                <div>
                    <label class="block text-xs font-medium text-neutral-400 mb-1">Board Title</label>
                    <input :value="editDiagramTitle"
                        @input="emit('update:editDiagramTitle', ($event.target as HTMLInputElement).value)" type="text"
                        placeholder="e.g. Core Topology Diagram"
                        class="w-full bg-neutral-900 border border-neutral-800 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-cyan-500" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-neutral-400 mb-1">Description</label>
                    <textarea :value="editDiagramDesc"
                        @input="emit('update:editDiagramDesc', ($event.target as HTMLTextAreaElement).value)" rows="3"
                        placeholder="Optional description..."
                        class="w-full bg-neutral-900 border border-neutral-800 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-cyan-500 resize-none"></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button @click="emit('closeEditDiagram')"
                        class="px-4 py-2 rounded-lg bg-neutral-900 text-neutral-300 text-xs font-medium">Cancel</button>
                    <button @click="emit('updateDiagram')"
                        class="px-4 py-2 rounded-lg bg-cyan-500 hover:bg-cyan-400 text-black text-xs font-semibold">Save
                        Board Title</button>
                </div>
            </div>
        </div>
    </div>
</template>
