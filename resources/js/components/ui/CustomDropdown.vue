<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';

interface Option {
    value: string | number;
    label: string;
    description?: string;
    badge?: string;
}

const props = defineProps<{
    modelValue: string | number | null;
    options: Option[];
    placeholder?: string;
    disabled?: boolean;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: string | number): void;
    (e: 'change', value: string | number): void;
}>();

const isOpen = ref(false);
const dropdownRef = ref<HTMLElement | null>(null);

const selectedOption = computed(() => {
    return props.options.find(opt => String(opt.value) === String(props.modelValue));
});

const toggleDropdown = () => {
    if (props.disabled) return;
    isOpen.value = !isOpen.value;
};

const selectOption = (opt: Option) => {
    emit('update:modelValue', opt.value);
    emit('change', opt.value);
    isOpen.value = false;
};

const handleClickOutside = (event: MouseEvent) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target as Node)) {
        isOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
    <div ref="dropdownRef" class="relative inline-block w-full min-w-0 flex-1">
        <!-- Trigger Button -->
        <button type="button" @click="toggleDropdown" :disabled="disabled"
            class="w-full flex items-center justify-between px-3 py-2 text-xs font-medium rounded-lg transition-all duration-200 border min-w-0"
            :class="[
                disabled
                    ? 'bg-neutral-900/40 border-neutral-850 text-neutral-600 cursor-not-allowed'
                    : isOpen
                        ? 'bg-neutral-900 border-cyan-500/60 text-neutral-100 shadow-[0_0_15px_rgba(6,182,212,0.15)]'
                        : 'bg-black/80 hover:bg-neutral-900/80 border-neutral-800 hover:border-neutral-700 text-neutral-200 shadow-sm'
            ]">
            <span class="truncate flex items-center gap-1.5 min-w-0 flex-1 text-left">
                <slot name="icon"></slot>
                <span v-if="selectedOption" class="truncate font-semibold text-neutral-100 block min-w-0">
                    {{ selectedOption.label }}
                </span>
                <span v-else class="text-neutral-500 truncate block min-w-0">
                    {{ placeholder || 'Select option...' }}
                </span>
            </span>

            <div class="flex items-center gap-1.5 shrink-0 ml-1">
                <span v-if="selectedOption?.badge"
                    class="px-1.5 py-0.5 text-[9px] uppercase font-bold rounded bg-cyan-950/80 border border-cyan-800/60 text-cyan-400 shrink-0">
                    {{ selectedOption.badge }}
                </span>
                <!-- Chevron -->
                <svg class="w-3.5 h-3.5 text-neutral-400 transition-transform duration-200 shrink-0"
                    :class="{ 'rotate-180 text-cyan-400': isOpen }" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </div>
        </button>

        <!-- Dropdown Menu Popover -->
        <Transition enter-active-class="transition duration-150 ease-out"
            enter-from-class="transform scale-95 opacity-0 -translate-y-1"
            enter-to-class="transform scale-100 opacity-100 translate-y-0"
            leave-active-class="transition duration-100 ease-in"
            leave-from-class="transform scale-100 opacity-100 translate-y-0"
            leave-to-class="transform scale-95 opacity-0 -translate-y-1">
            <div v-if="isOpen"
                class="absolute left-0 z-50 mt-1.5 max-h-64 overflow-y-auto rounded-xl bg-black/95 border border-neutral-800 shadow-[0_10px_30px_rgba(0,0,0,0.9)] backdrop-blur-md p-1.5 font-sans text-xs min-w-[260px] max-w-md w-max max-w-[90vw] scrollbar-thin scrollbar-thumb-neutral-800">
                <div v-if="options.length === 0" class="px-3 py-2 text-neutral-500 text-center">
                    No options available
                </div>
                <button v-for="opt in options" :key="opt.value" type="button" @click="selectOption(opt)"
                    class="w-full flex items-start justify-between px-3 py-2.5 text-left rounded-lg transition-colors group mb-0.5"
                    :class="[
                        String(opt.value) === String(modelValue)
                            ? 'bg-neutral-850 text-cyan-400 font-semibold'
                            : 'text-neutral-300 hover:bg-neutral-900 hover:text-white'
                    ]">
                    <div class="flex flex-col min-w-0 pr-3 flex-1">
                        <span class="text-xs font-medium leading-snug whitespace-normal break-words">{{ opt.label
                            }}</span>
                        <span v-if="opt.description"
                            class="text-[10px] text-neutral-500 leading-snug whitespace-normal break-words mt-0.5 group-hover:text-neutral-400">
                            {{ opt.description }}
                        </span>
                    </div>

                    <div class="flex items-center gap-1.5 shrink-0 pt-0.5">
                        <span v-if="opt.badge"
                            class="px-1.5 py-0.5 text-[9px] font-semibold rounded bg-neutral-900 border border-neutral-800 text-neutral-400 group-hover:border-neutral-700">
                            {{ opt.badge }}
                        </span>
                        <!-- Active Checkmark -->
                        <svg v-if="String(opt.value) === String(modelValue)" class="w-4 h-4 text-cyan-400 shrink-0"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </button>
            </div>
        </Transition>
    </div>
</template>
