<script setup lang="ts">
import { Trash2, X } from '@lucide/vue';

const props = defineProps<{
    show: boolean;
    nodeLabel?: string;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'confirm'): void;
}>();
</script>

<template>
    <div v-if="show" class="fixed inset-0 bg-black/85 backdrop-blur-md flex items-center justify-center z-50 p-4">
        <div class="w-full max-w-md bg-black border border-neutral-800 rounded-2xl p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-neutral-800 pb-3">
                <div class="flex items-center gap-2">
                    <Trash2 class="w-5 h-5 text-rose-400" />
                    <h3 class="font-bold text-base text-white">Delete Architecture Node?</h3>
                </div>
                <button @click="emit('close')" class="text-neutral-400 hover:text-white">
                    <X class="w-5 h-5" />
                </button>
            </div>
            <p class="text-xs text-neutral-300">
                Are you sure you want to delete node <strong class="text-cyan-300">{{ nodeLabel || 'this node'
                    }}</strong>? This will permanently remove the node and any connected topology edges.
            </p>
            <div class="flex justify-end gap-3 pt-2">
                <button @click="emit('close')"
                    class="px-4 py-2 rounded-lg bg-neutral-900 text-neutral-300 text-xs font-medium hover:bg-neutral-800">
                    Cancel
                </button>
                <button @click="emit('confirm')"
                    class="px-4 py-2 rounded-lg bg-rose-600 hover:bg-rose-500 text-white text-xs font-semibold shadow-lg shadow-rose-600/20">
                    Delete Node
                </button>
            </div>
        </div>
    </div>
</template>
