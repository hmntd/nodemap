<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { toPng, toJpeg } from 'html-to-image';
import {
    FileCode,
    Image as ImageIcon,
    Loader2,
    Download,
    Check,
    X
} from '@lucide/vue';

const props = defineProps<{
    show: boolean;
    currentDiagram: any;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const exportFormat = ref<'png' | 'jpeg' | 'mermaid' | 'plantuml' | 'json'>('png');
const copiedExport = ref(false);
const isExportingImage = ref(false);
const imageExportUrl = ref<string>('');

const exportedCode = computed(() => {
    if (!props.currentDiagram) return '';
    const diagramNodes = props.currentDiagram.nodes || [];
    const diagramEdges = props.currentDiagram.edges || [];

    if (exportFormat.value === 'mermaid') {
        let code = 'graph TD\n';
        diagramNodes.forEach((n: any) => {
            const cleanLabel = n.label.replace(/"/g, '');
            code += `    N${n.id}["${cleanLabel} (${n.type})"]\n`;
        });
        diagramEdges.forEach((e: any) => {
            const labelStr = e.label ? `|"${e.label}"|` : '';
            code += `    N${e.source_node_id} -->${labelStr} N${e.target_node_id}\n`;
        });
        return code;
    } else if (exportFormat.value === 'plantuml') {
        let code = '@startuml\n';
        diagramNodes.forEach((n: any) => {
            code += `component "${n.label}" as N${n.id} <<${n.type}>>\n`;
        });
        diagramEdges.forEach((e: any) => {
            const labelStr = e.label ? `: ${e.label}` : '';
            code += `N${e.source_node_id} --> N${e.target_node_id} ${labelStr}\n`;
        });
        code += '@enduml';
        return code;
    } else {
        return JSON.stringify(props.currentDiagram, null, 2);
    }
});

watch([exportFormat, () => props.show], async ([newFormat, isShown]) => {
    if (isShown && (newFormat === 'png' || newFormat === 'jpeg')) {
        await generateImagePreview(newFormat as 'png' | 'jpeg');
    }
});

async function generateImagePreview(format: 'png' | 'jpeg') {
    isExportingImage.value = true;
    imageExportUrl.value = '';
    try {
        const el = document.querySelector('.vue-flow') as HTMLElement;
        if (!el) return;

        const options = {
            backgroundColor: '#09090b',
            quality: 0.95,
            cacheBust: true,
            filter: (domNode: HTMLElement) => {
                if (domNode.classList && (
                    domNode.classList.contains('vue-flow__controls') ||
                    domNode.classList.contains('vue-flow__minimap')
                )) {
                    return false;
                }
                return true;
            }
        };

        const dataUrl = format === 'png'
            ? await toPng(el, options)
            : await toJpeg(el, options);

        imageExportUrl.value = dataUrl;
    } catch (err) {
        console.error('Failed to generate image preview:', err);
    } finally {
        isExportingImage.value = false;
    }
}

async function downloadDiagramImage() {
    const format = (exportFormat.value === 'jpeg' ? 'jpeg' : 'png');
    const el = document.querySelector('.vue-flow') as HTMLElement;
    if (!el) return;

    isExportingImage.value = true;
    try {
        const options = {
            backgroundColor: '#09090b',
            quality: 0.95,
            cacheBust: true,
            filter: (domNode: HTMLElement) => {
                if (domNode.classList && (
                    domNode.classList.contains('vue-flow__controls') ||
                    domNode.classList.contains('vue-flow__minimap')
                )) {
                    return false;
                }
                return true;
            }
        };

        const dataUrl = format === 'png'
            ? await toPng(el, options)
            : await toJpeg(el, options);

        const titleStr = props.currentDiagram?.title ? props.currentDiagram.title.toLowerCase().replace(/[^a-z0-9]/g, '-') : 'architecture-schema';
        const link = document.createElement('a');
        link.download = `${titleStr}.${format}`;
        link.href = dataUrl;
        link.click();
    } catch (err) {
        console.error('Failed to download image:', err);
    } finally {
        isExportingImage.value = false;
    }
}

function copyExportCode() {
    navigator.clipboard.writeText(exportedCode.value);
    copiedExport.value = true;
    setTimeout(() => { copiedExport.value = false; }, 2000);
}
</script>

<template>
    <div v-if="show" class="fixed inset-0 bg-black/85 backdrop-blur-md flex items-center justify-center z-50 p-4">
        <div class="w-full max-w-2xl bg-black border border-neutral-800 rounded-2xl p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-neutral-800 pb-3">
                <div class="flex items-center gap-2">
                    <ImageIcon v-if="exportFormat === 'png' || exportFormat === 'jpeg'"
                        class="w-5 h-5 text-indigo-400" />
                    <FileCode v-else class="w-5 h-5 text-indigo-400" />
                    <h3 class="font-bold text-base text-neutral-100">Export Architecture Diagram</h3>
                </div>
                <button @click="emit('close')" class="text-neutral-400 hover:text-white">
                    <X class="w-5 h-5" />
                </button>
            </div>

            <div class="flex flex-wrap gap-2">
                <button @click="exportFormat = 'png'"
                    class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5"
                    :class="exportFormat === 'png' ? 'bg-indigo-600 text-white' : 'bg-neutral-900 text-neutral-400 hover:text-neutral-200'">
                    <ImageIcon class="w-3.5 h-3.5" />
                    <span>PNG Image</span>
                </button>
                <button @click="exportFormat = 'jpeg'"
                    class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5"
                    :class="exportFormat === 'jpeg' ? 'bg-indigo-600 text-white' : 'bg-neutral-900 text-neutral-400 hover:text-neutral-200'">
                    <ImageIcon class="w-3.5 h-3.5" />
                    <span>JPEG Image</span>
                </button>
                <button @click="exportFormat = 'mermaid'"
                    class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5"
                    :class="exportFormat === 'mermaid' ? 'bg-indigo-600 text-white' : 'bg-neutral-900 text-neutral-400 hover:text-neutral-200'">
                    <FileCode class="w-3.5 h-3.5" />
                    <span>Mermaid.js</span>
                </button>
                <button @click="exportFormat = 'plantuml'"
                    class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5"
                    :class="exportFormat === 'plantuml' ? 'bg-indigo-600 text-white' : 'bg-neutral-900 text-neutral-400 hover:text-neutral-200'">
                    <FileCode class="w-3.5 h-3.5" />
                    <span>PlantUML</span>
                </button>
                <button @click="exportFormat = 'json'"
                    class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5"
                    :class="exportFormat === 'json' ? 'bg-indigo-600 text-white' : 'bg-neutral-900 text-neutral-400 hover:text-neutral-200'">
                    <FileCode class="w-3.5 h-3.5" />
                    <span>JSON Schema</span>
                </button>
            </div>

            <!-- IMAGE PREVIEW -->
            <div v-if="exportFormat === 'png' || exportFormat === 'jpeg'"
                class="relative bg-neutral-950 rounded-xl border border-neutral-800 p-3 flex items-center justify-center min-h-[260px] max-h-[380px] overflow-hidden">
                <div v-if="isExportingImage" class="flex flex-col items-center gap-3 text-neutral-400 py-12">
                    <Loader2 class="w-8 h-8 animate-spin text-indigo-500" />
                    <span class="text-xs font-medium">Generating image snapshot...</span>
                </div>
                <img v-else-if="imageExportUrl" :src="imageExportUrl"
                    class="max-h-[350px] w-auto max-w-full object-contain rounded-lg border border-neutral-800 shadow-xl"
                    alt="Schema export preview" />
                <div v-else class="text-neutral-500 text-xs py-12">
                    Failed to render diagram image preview.
                </div>
            </div>

            <!-- CODE TEXTAREA -->
            <div v-else class="relative">
                <textarea readonly :value="exportedCode" rows="12"
                    class="w-full bg-neutral-950 font-mono text-xs text-cyan-300 p-4 rounded-xl border border-neutral-800 focus:outline-none resize-none"></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-2 border-t border-neutral-900">
                <button @click="emit('close')"
                    class="px-4 py-2 rounded-lg bg-neutral-900 text-neutral-300 text-xs font-medium hover:bg-neutral-800">
                    Close
                </button>

                <!-- DOWNLOAD IMAGE BUTTON -->
                <button v-if="exportFormat === 'png' || exportFormat === 'jpeg'" @click="downloadDiagramImage"
                    :disabled="isExportingImage || !imageExportUrl"
                    class="px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 text-white text-xs font-semibold flex items-center gap-2 transition">
                    <Download class="w-4 h-4" />
                    <span>Download {{ exportFormat.toUpperCase() }}</span>
                </button>

                <!-- COPY CODE BUTTON -->
                <button v-else @click="copyExportCode"
                    class="px-4 py-2 rounded-lg bg-cyan-500 hover:bg-cyan-400 text-black text-xs font-semibold flex items-center gap-2 transition">
                    <Check v-if="copiedExport" class="w-4 h-4 text-black" />
                    <span>{{ copiedExport ? 'Copied to Clipboard!' : 'Copy Code' }}</span>
                </button>
            </div>
        </div>
    </div>
</template>
