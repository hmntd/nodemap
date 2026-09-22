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
    <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/85 p-4 backdrop-blur-md"
    >
        <div
            class="w-full max-w-md space-y-4 rounded-2xl border border-neutral-800 bg-black p-6 shadow-2xl"
        >
            <div
                class="flex items-center justify-between border-b border-neutral-800 pb-3"
            >
                <div class="flex items-center gap-2">
                    <Trash2 class="h-5 w-5 text-rose-400" />
                    <h3 class="text-base font-bold text-white">
                        Delete Architecture Node?
                    </h3>
                </div>
                <button
                    @click="emit('close')"
                    class="text-neutral-400 hover:text-white"
                >
                    <X class="h-5 w-5" />
                </button>
            </div>
            <p class="text-xs text-neutral-300">
                Are you sure you want to delete node
                <strong class="text-cyan-300">{{
                    nodeLabel || 'this node'
                }}</strong
                >? This will permanently remove the node and any connected
                topology edges.
            </p>
            <div class="flex justify-end gap-3 pt-2">
                <button
                    @click="emit('close')"
                    class="rounded-lg bg-neutral-900 px-4 py-2 text-xs font-medium text-neutral-300 hover:bg-neutral-800"
                >
                    Cancel
                </button>
                <button
                    @click="emit('confirm')"
                    class="rounded-lg bg-rose-600 px-4 py-2 text-xs font-semibold text-white shadow-lg shadow-rose-600/20 hover:bg-rose-500"
                >
                    Delete Node
                </button>
            </div>
        </div>
    </div>
</template>
