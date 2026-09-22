<script setup lang="ts">
import { Settings, X, Trash2 } from '@lucide/vue';

const props = defineProps<{
    selectedElement: { type: 'node' | 'edge'; item: any } | null;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'saveNode'): void;
    (e: 'promptDeleteNode'): void;
    (e: 'saveEdge'): void;
    (e: 'deleteEdge'): void;
}>();
</script>

<template>
    <aside
        v-if="selectedElement"
        class="z-20 flex w-72 shrink-0 flex-col overflow-y-auto border-l border-neutral-800 bg-black p-5"
    >
        <div
            class="flex items-center justify-between border-b border-neutral-800 pb-4"
        >
            <div class="flex items-center gap-2">
                <Settings class="h-4 w-4 text-cyan-400" />
                <span
                    class="text-sm font-semibold tracking-wider text-neutral-100 uppercase"
                    >Inspector</span
                >
            </div>
            <button
                @click="emit('close')"
                class="rounded p-1 text-neutral-400 hover:text-white"
            >
                <X class="h-4 w-4" />
            </button>
        </div>

        <!-- NODE INSPECTOR -->
        <div v-if="selectedElement.type === 'node'" class="space-y-4 pt-4">
            <div>
                <label class="mb-1 block text-xs font-medium text-neutral-400"
                    >Node Title</label
                >
                <input
                    v-model="selectedElement.item.label"
                    type="text"
                    class="w-full rounded-lg border border-neutral-800 bg-neutral-900 px-3 py-2 text-xs text-neutral-100 focus:border-cyan-500 focus:outline-none"
                />
            </div>

            <div>
                <label class="mb-1 block text-xs font-medium text-neutral-400"
                    >Architecture Type</label
                >
                <select
                    v-model="selectedElement.item.type"
                    class="w-full rounded-lg border border-neutral-800 bg-neutral-900 px-3 py-2 text-xs text-neutral-100 focus:border-cyan-500 focus:outline-none"
                >
                    <option value="service">Microservice</option>
                    <option value="database">Database</option>
                    <option value="queue">Message Queue</option>
                    <option value="client">Client App</option>
                    <option value="external_api">External API / Gateway</option>
                </select>
            </div>

            <!-- Metadata Fields -->
            <div class="space-y-3 border-t border-neutral-900 pt-4">
                <span class="block text-xs font-semibold text-neutral-300"
                    >Metadata</span
                >

                <div>
                    <label class="mb-1 block text-[11px] text-neutral-400"
                        >Technology Stack</label
                    >
                    <input
                        v-model="selectedElement.item.metadata.technology"
                        type="text"
                        placeholder="e.g. Laravel / Go / PostgreSQL"
                        class="w-full rounded border border-neutral-800 bg-neutral-900 px-2.5 py-1.5 text-xs text-neutral-100 focus:border-cyan-500 focus:outline-none"
                    />
                </div>

                <div>
                    <label class="mb-1 block text-[11px] text-neutral-400"
                        >Port</label
                    >
                    <input
                        v-model="selectedElement.item.metadata.port"
                        type="text"
                        placeholder="e.g. 8080"
                        class="w-full rounded border border-neutral-800 bg-neutral-900 px-2.5 py-1.5 text-xs text-neutral-100 focus:border-cyan-500 focus:outline-none"
                    />
                </div>

                <div>
                    <label class="mb-1 block text-[11px] text-neutral-400"
                        >Replicas</label
                    >
                    <input
                        v-model="selectedElement.item.metadata.replicas"
                        type="number"
                        placeholder="e.g. 3"
                        class="w-full rounded border border-neutral-800 bg-neutral-900 px-2.5 py-1.5 text-xs text-neutral-100 focus:border-cyan-500 focus:outline-none"
                    />
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col gap-2 pt-4">
                <button
                    @click="emit('saveNode')"
                    class="w-full rounded-lg bg-cyan-500 py-2 text-xs font-semibold text-black transition hover:bg-cyan-400"
                >
                    Save Node Changes
                </button>
                <button
                    @click="emit('promptDeleteNode')"
                    class="flex w-full items-center justify-center gap-1.5 rounded-lg border border-rose-800/50 bg-rose-950/60 py-2 text-xs font-semibold text-rose-300 transition hover:bg-rose-900"
                >
                    <Trash2 class="h-3.5 w-3.5" />
                    <span>Delete Node</span>
                </button>
            </div>
        </div>

        <!-- EDGE INSPECTOR -->
        <div v-else-if="selectedElement.type === 'edge'" class="space-y-4 pt-4">
            <div>
                <label class="mb-1 block text-xs font-medium text-neutral-400"
                    >Connection Label</label
                >
                <input
                    v-model="selectedElement.item.label"
                    type="text"
                    placeholder="e.g. REST API / gRPC Call"
                    class="w-full rounded-lg border border-neutral-800 bg-neutral-900 px-3 py-2 text-xs text-neutral-100 focus:border-cyan-500 focus:outline-none"
                />
            </div>

            <div>
                <label class="mb-1 block text-xs font-medium text-neutral-400"
                    >Protocol Type</label
                >
                <select
                    v-model="selectedElement.item.type"
                    class="w-full rounded-lg border border-neutral-800 bg-neutral-900 px-3 py-2 text-xs text-neutral-100 focus:border-cyan-500 focus:outline-none"
                >
                    <option value="HTTP">HTTP / HTTPS</option>
                    <option value="gRPC">gRPC</option>
                    <option value="TCP">TCP / Socket</option>
                    <option value="WebSocket">WebSocket</option>
                    <option value="Queue">Message Queue</option>
                </select>
            </div>

            <div class="flex flex-col gap-2 pt-4">
                <button
                    @click="emit('saveEdge')"
                    class="w-full rounded-lg bg-cyan-500 py-2 text-xs font-semibold text-black transition hover:bg-cyan-400"
                >
                    Save Connection
                </button>
                <button
                    @click="emit('deleteEdge')"
                    class="flex w-full items-center justify-center gap-1.5 rounded-lg border border-rose-800/50 bg-rose-950/60 py-2 text-xs font-semibold text-rose-300 transition hover:bg-rose-900"
                >
                    <Trash2 class="h-3.5 w-3.5" />
                    <span>Delete Connection</span>
                </button>
            </div>
        </div>
    </aside>
</template>
