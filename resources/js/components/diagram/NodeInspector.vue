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
    <aside v-if="selectedElement"
        class="w-72 border-l border-neutral-800 bg-black flex flex-col z-20 shrink-0 p-5 overflow-y-auto">
        <div class="flex items-center justify-between pb-4 border-b border-neutral-800">
            <div class="flex items-center gap-2">
                <Settings class="w-4 h-4 text-cyan-400" />
                <span class="text-sm font-semibold text-neutral-100 uppercase tracking-wider">Inspector</span>
            </div>
            <button @click="emit('close')" class="text-neutral-400 hover:text-white p-1 rounded">
                <X class="w-4 h-4" />
            </button>
        </div>

        <!-- NODE INSPECTOR -->
        <div v-if="selectedElement.type === 'node'" class="space-y-4 pt-4">
            <div>
                <label class="block text-xs font-medium text-neutral-400 mb-1">Node Title</label>
                <input v-model="selectedElement.item.label" type="text"
                    class="w-full bg-neutral-900 border border-neutral-800 rounded-lg px-3 py-2 text-xs text-neutral-100 focus:outline-none focus:border-cyan-500" />
            </div>

            <div>
                <label class="block text-xs font-medium text-neutral-400 mb-1">Architecture Type</label>
                <select v-model="selectedElement.item.type"
                    class="w-full bg-neutral-900 border border-neutral-800 rounded-lg px-3 py-2 text-xs text-neutral-100 focus:outline-none focus:border-cyan-500">
                    <option value="service">Microservice</option>
                    <option value="database">Database</option>
                    <option value="queue">Message Queue</option>
                    <option value="client">Client App</option>
                    <option value="external_api">External API / Gateway</option>
                </select>
            </div>

            <!-- Metadata Fields -->
            <div class="border-t border-neutral-900 pt-4 space-y-3">
                <span class="text-xs font-semibold text-neutral-300 block">Metadata</span>

                <div>
                    <label class="block text-[11px] text-neutral-400 mb-1">Technology Stack</label>
                    <input v-model="selectedElement.item.metadata.technology" type="text"
                        placeholder="e.g. Laravel / Go / PostgreSQL"
                        class="w-full bg-neutral-900 border border-neutral-800 rounded px-2.5 py-1.5 text-xs text-neutral-100 focus:outline-none focus:border-cyan-500" />
                </div>

                <div>
                    <label class="block text-[11px] text-neutral-400 mb-1">Port</label>
                    <input v-model="selectedElement.item.metadata.port" type="text" placeholder="e.g. 8080"
                        class="w-full bg-neutral-900 border border-neutral-800 rounded px-2.5 py-1.5 text-xs text-neutral-100 focus:outline-none focus:border-cyan-500" />
                </div>

                <div>
                    <label class="block text-[11px] text-neutral-400 mb-1">Replicas</label>
                    <input v-model="selectedElement.item.metadata.replicas" type="number" placeholder="e.g. 3"
                        class="w-full bg-neutral-900 border border-neutral-800 rounded px-2.5 py-1.5 text-xs text-neutral-100 focus:outline-none focus:border-cyan-500" />
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="pt-4 flex flex-col gap-2">
                <button @click="emit('saveNode')"
                    class="w-full py-2 bg-cyan-500 hover:bg-cyan-400 text-black font-semibold text-xs rounded-lg transition">
                    Save Node Changes
                </button>
                <button @click="emit('promptDeleteNode')"
                    class="w-full py-2 bg-rose-950/60 hover:bg-rose-900 border border-rose-800/50 text-rose-300 font-semibold text-xs rounded-lg transition flex items-center justify-center gap-1.5">
                    <Trash2 class="w-3.5 h-3.5" />
                    <span>Delete Node</span>
                </button>
            </div>
        </div>

        <!-- EDGE INSPECTOR -->
        <div v-else-if="selectedElement.type === 'edge'" class="space-y-4 pt-4">
            <div>
                <label class="block text-xs font-medium text-neutral-400 mb-1">Connection Label</label>
                <input v-model="selectedElement.item.label" type="text" placeholder="e.g. REST API / gRPC Call"
                    class="w-full bg-neutral-900 border border-neutral-800 rounded-lg px-3 py-2 text-xs text-neutral-100 focus:outline-none focus:border-cyan-500" />
            </div>

            <div>
                <label class="block text-xs font-medium text-neutral-400 mb-1">Protocol Type</label>
                <select v-model="selectedElement.item.type"
                    class="w-full bg-neutral-900 border border-neutral-800 rounded-lg px-3 py-2 text-xs text-neutral-100 focus:outline-none focus:border-cyan-500">
                    <option value="HTTP">HTTP / HTTPS</option>
                    <option value="gRPC">gRPC</option>
                    <option value="TCP">TCP / Socket</option>
                    <option value="WebSocket">WebSocket</option>
                    <option value="Queue">Message Queue</option>
                </select>
            </div>

            <div class="pt-4 flex flex-col gap-2">
                <button @click="emit('saveEdge')"
                    class="w-full py-2 bg-cyan-500 hover:bg-cyan-400 text-black font-semibold text-xs rounded-lg transition">
                    Save Connection
                </button>
                <button @click="emit('deleteEdge')"
                    class="w-full py-2 bg-rose-950/60 hover:bg-rose-900 border border-rose-800/50 text-rose-300 font-semibold text-xs rounded-lg transition flex items-center justify-center gap-1.5">
                    <Trash2 class="w-3.5 h-3.5" />
                    <span>Delete Connection</span>
                </button>
            </div>
        </div>
    </aside>
</template>
