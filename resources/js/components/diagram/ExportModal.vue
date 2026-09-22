<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { toPng, toJpeg } from 'html-to-image';
import {
    FileCode,
    Image as ImageIcon,
    Loader2,
    Download,
    Check,
    X,
} from '@lucide/vue';

const props = defineProps<{
    show: boolean;
    currentDiagram: any;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const exportFormat = ref<'png' | 'jpeg' | 'mermaid' | 'plantuml' | 'json'>(
    'png',
);
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
                if (
                    domNode.classList &&
                    (domNode.classList.contains('vue-flow__controls') ||
                        domNode.classList.contains('vue-flow__minimap'))
                ) {
                    return false;
                }
                return true;
            },
        };

        const dataUrl =
            format === 'png'
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
    const format = exportFormat.value === 'jpeg' ? 'jpeg' : 'png';
    const el = document.querySelector('.vue-flow') as HTMLElement;
    if (!el) return;

    isExportingImage.value = true;
    try {
        const options = {
            backgroundColor: '#09090b',
            quality: 0.95,
            cacheBust: true,
            filter: (domNode: HTMLElement) => {
                if (
                    domNode.classList &&
                    (domNode.classList.contains('vue-flow__controls') ||
                        domNode.classList.contains('vue-flow__minimap'))
                ) {
                    return false;
                }
                return true;
            },
        };

        const dataUrl =
            format === 'png'
                ? await toPng(el, options)
                : await toJpeg(el, options);

        const titleStr = props.currentDiagram?.title
            ? props.currentDiagram.title
                  .toLowerCase()
                  .replace(/[^a-z0-9]/g, '-')
            : 'architecture-schema';
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
    setTimeout(() => {
        copiedExport.value = false;
    }, 2000);
}
</script>

<template>
    <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/85 p-4 backdrop-blur-md"
    >
        <div
            class="w-full max-w-2xl space-y-4 rounded-2xl border border-neutral-800 bg-black p-6 shadow-2xl"
        >
            <div
                class="flex items-center justify-between border-b border-neutral-800 pb-3"
            >
                <div class="flex items-center gap-2">
                    <ImageIcon
                        v-if="exportFormat === 'png' || exportFormat === 'jpeg'"
                        class="h-5 w-5 text-indigo-400"
                    />
                    <FileCode v-else class="h-5 w-5 text-indigo-400" />
                    <h3 class="text-base font-bold text-neutral-100">
                        Export Architecture Diagram
                    </h3>
                </div>
                <button
                    @click="emit('close')"
                    class="text-neutral-400 hover:text-white"
                >
                    <X class="h-5 w-5" />
                </button>
            </div>

            <div class="flex flex-wrap gap-2">
                <button
                    @click="exportFormat = 'png'"
                    class="flex items-center gap-1.5 rounded-lg px-3.5 py-1.5 text-xs font-semibold transition"
                    :class="
                        exportFormat === 'png'
                            ? 'bg-indigo-600 text-white'
                            : 'bg-neutral-900 text-neutral-400 hover:text-neutral-200'
                    "
                >
                    <ImageIcon class="h-3.5 w-3.5" />
                    <span>PNG Image</span>
                </button>
                <button
                    @click="exportFormat = 'jpeg'"
                    class="flex items-center gap-1.5 rounded-lg px-3.5 py-1.5 text-xs font-semibold transition"
                    :class="
                        exportFormat === 'jpeg'
                            ? 'bg-indigo-600 text-white'
                            : 'bg-neutral-900 text-neutral-400 hover:text-neutral-200'
                    "
                >
                    <ImageIcon class="h-3.5 w-3.5" />
                    <span>JPEG Image</span>
                </button>
                <button
                    @click="exportFormat = 'mermaid'"
                    class="flex items-center gap-1.5 rounded-lg px-3.5 py-1.5 text-xs font-semibold transition"
                    :class="
                        exportFormat === 'mermaid'
                            ? 'bg-indigo-600 text-white'
                            : 'bg-neutral-900 text-neutral-400 hover:text-neutral-200'
                    "
                >
                    <FileCode class="h-3.5 w-3.5" />
                    <span>Mermaid.js</span>
                </button>
                <button
                    @click="exportFormat = 'plantuml'"
                    class="flex items-center gap-1.5 rounded-lg px-3.5 py-1.5 text-xs font-semibold transition"
                    :class="
                        exportFormat === 'plantuml'
                            ? 'bg-indigo-600 text-white'
                            : 'bg-neutral-900 text-neutral-400 hover:text-neutral-200'
                    "
                >
                    <FileCode class="h-3.5 w-3.5" />
                    <span>PlantUML</span>
                </button>
                <button
                    @click="exportFormat = 'json'"
                    class="flex items-center gap-1.5 rounded-lg px-3.5 py-1.5 text-xs font-semibold transition"
                    :class="
                        exportFormat === 'json'
                            ? 'bg-indigo-600 text-white'
                            : 'bg-neutral-900 text-neutral-400 hover:text-neutral-200'
                    "
                >
                    <FileCode class="h-3.5 w-3.5" />
                    <span>JSON Schema</span>
                </button>
            </div>

            <!-- IMAGE PREVIEW -->
            <div
                v-if="exportFormat === 'png' || exportFormat === 'jpeg'"
                class="relative flex max-h-[380px] min-h-[260px] items-center justify-center overflow-hidden rounded-xl border border-neutral-800 bg-neutral-950 p-3"
            >
                <div
                    v-if="isExportingImage"
                    class="flex flex-col items-center gap-3 py-12 text-neutral-400"
                >
                    <Loader2 class="h-8 w-8 animate-spin text-indigo-500" />
                    <span class="text-xs font-medium"
                        >Generating image snapshot...</span
                    >
                </div>
                <img
                    v-else-if="imageExportUrl"
                    :src="imageExportUrl"
                    class="max-h-[350px] w-auto max-w-full rounded-lg border border-neutral-800 object-contain shadow-xl"
                    alt="Schema export preview"
                />
                <div v-else class="py-12 text-xs text-neutral-500">
                    Failed to render diagram image preview.
                </div>
            </div>

            <!-- CODE TEXTAREA -->
            <div v-else class="relative">
                <textarea
                    readonly
                    :value="exportedCode"
                    rows="12"
                    class="w-full resize-none rounded-xl border border-neutral-800 bg-neutral-950 p-4 font-mono text-xs text-cyan-300 focus:outline-none"
                ></textarea>
            </div>

            <div
                class="flex justify-end gap-3 border-t border-neutral-900 pt-2"
            >
                <button
                    @click="emit('close')"
                    class="rounded-lg bg-neutral-900 px-4 py-2 text-xs font-medium text-neutral-300 hover:bg-neutral-800"
                >
                    Close
                </button>

                <!-- DOWNLOAD IMAGE BUTTON -->
                <button
                    v-if="exportFormat === 'png' || exportFormat === 'jpeg'"
                    @click="downloadDiagramImage"
                    :disabled="isExportingImage || !imageExportUrl"
                    class="flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-indigo-500 disabled:opacity-50"
                >
                    <Download class="h-4 w-4" />
                    <span>Download {{ exportFormat.toUpperCase() }}</span>
                </button>

                <!-- COPY CODE BUTTON -->
                <button
                    v-else
                    @click="copyExportCode"
                    class="flex items-center gap-2 rounded-lg bg-cyan-500 px-4 py-2 text-xs font-semibold text-black transition hover:bg-cyan-400"
                >
                    <Check v-if="copiedExport" class="h-4 w-4 text-black" />
                    <span>{{
                        copiedExport ? 'Copied to Clipboard!' : 'Copy Code'
                    }}</span>
                </button>
            </div>
        </div>
    </div>
</template>
