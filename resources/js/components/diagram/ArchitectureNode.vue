<script setup lang="ts">
import { computed } from 'vue';
import { Handle, Position } from '@vue-flow/core';
import {
    Server,
    Database,
    Layers,
    Monitor,
    Globe
} from '@lucide/vue';

const props = defineProps<{
    id: string;
    label: string;
    type: string;
    selected?: boolean;
    data?: {
        label?: string;
        type?: string;
        metadata?: Record<string, any>;
    };
}>();

const nodeType = computed(() => props.data?.type || props.type || 'service');
const nodeLabel = computed(() => props.data?.label || props.label || 'Node');
const metadata = computed(() => props.data?.metadata || {});

const typeConfig = computed(() => {
    switch (nodeType.value) {
        case 'database':
            return {
                icon: Database,
                badgeBg: 'bg-indigo-950/60 text-indigo-400 border-indigo-800/40',
                border: 'border-neutral-800 hover:border-indigo-500/70',
                glow: 'shadow-[0_0_25px_rgba(99,102,241,0.12)]',
                title: 'Database Cluster',
            };
        case 'queue':
            return {
                icon: Layers,
                badgeBg: 'bg-amber-950/60 text-amber-400 border-amber-800/40',
                border: 'border-neutral-800 hover:border-amber-500/70',
                glow: 'shadow-[0_0_25px_rgba(245,158,11,0.12)]',
                title: 'Message Queue / Broker',
            };
        case 'client':
            return {
                icon: Monitor,
                badgeBg: 'bg-purple-950/60 text-purple-400 border-purple-800/40',
                border: 'border-neutral-800 hover:border-purple-500/70',
                glow: 'shadow-[0_0_25px_rgba(168,85,247,0.12)]',
                title: 'Client Application',
            };
        case 'external_api':
            return {
                icon: Globe,
                badgeBg: 'bg-rose-950/60 text-rose-400 border-rose-800/40',
                border: 'border-neutral-800 hover:border-rose-500/70',
                glow: 'shadow-[0_0_25px_rgba(244,63,94,0.12)]',
                title: 'API Gateway / Cloud API',
            };
        case 'service':
        default:
            return {
                icon: Server,
                badgeBg: 'bg-emerald-950/60 text-emerald-400 border-emerald-800/40',
                border: 'border-neutral-800 hover:border-emerald-500/70',
                glow: 'shadow-[0_0_25px_rgba(16,185,129,0.12)]',
                title: 'Microservice',
            };
    }
});

const metadataFields = computed(() => {
    const meta = metadata.value || {};
    const knownConfig: Record<string, { label: string; class: string }> = {
        technology: { label: 'Stack', class: 'text-neutral-200 font-mono bg-neutral-900 px-1.5 py-0.5 rounded border border-neutral-800' },
        port: { label: 'Port', class: 'text-cyan-300 font-mono' },
        replicas: { label: 'Replicas', class: 'text-emerald-300 font-mono' },
        protocol: { label: 'Protocol', class: 'text-purple-300 font-mono' },
    };

    return Object.entries(meta)
        .filter(([_, value]) => value !== null && value !== undefined && value !== '')
        .map(([key, value]) => {
            const config = knownConfig[key];
            return {
                key,
                label: config?.label || (key.charAt(0).toUpperCase() + key.slice(1)),
                value: String(value),
                class: config?.class || 'text-neutral-300 font-mono'
            };
        });
});
</script>

<template>
    <div class="relative min-w-[220px] rounded-xl bg-black border p-4 transition-all duration-200 select-none text-neutral-100"
        :class="[
            typeConfig.border,
            typeConfig.glow,
            selected ? 'ring-2 ring-cyan-400 scale-[1.02] shadow-cyan-500/20 shadow-xl' : 'shadow-2xl'
        ]">
        <!-- Connection Handles -->
        <Handle id="top" type="target" :position="Position.Top" class="!bg-cyan-400 !w-3 !h-3 !border-2 !border-black" />
        <Handle id="bottom" type="source" :position="Position.Bottom" class="!bg-cyan-400 !w-3 !h-3 !border-2 !border-black" />
        <Handle id="left" type="target" :position="Position.Left" class="!bg-cyan-400 !w-3 !h-3 !border-2 !border-black" />
        <Handle id="right" type="source" :position="Position.Right" class="!bg-cyan-400 !w-3 !h-3 !border-2 !border-black" />

        <!-- Node Header -->
        <div class="flex items-center justify-between gap-3 mb-2">
            <div class="flex items-center gap-2">
                <div class="p-2 rounded-lg bg-neutral-900 border border-neutral-800 text-neutral-200">
                    <component :is="typeConfig.icon" class="w-4 h-4 text-cyan-400" />
                </div>
                <span class="text-xs font-semibold uppercase tracking-wider px-2 py-0.5 rounded-full border text-[10px]"
                    :class="typeConfig.badgeBg">
                    {{ nodeType.replace('_', ' ') }}
                </span>
            </div>
            <div
                class="flex items-center gap-1.5 text-xs text-emerald-400 bg-emerald-950/40 border border-emerald-900/50 px-2 py-0.5 rounded-full">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                <span class="text-[10px] font-medium uppercase tracking-tight">Active</span>
            </div>
        </div>

        <!-- Node Title -->
        <div class="font-bold text-sm text-neutral-100 tracking-tight mb-2">
            {{ nodeLabel }}
        </div>

        <!-- Metadata Pills -->
        <div v-if="metadataFields.length > 0" class="space-y-1.5 text-xs border-t border-neutral-900 pt-2.5 mt-2">
            <div 
                v-for="field in metadataFields" 
                :key="field.key" 
                class="flex items-center justify-between text-[11px]"
            >
                <span class="text-neutral-400 font-medium">{{ field.label }}:</span>
                <span :class="field.class">{{ field.value }}</span>
            </div>
        </div>
    </div>
</template>
