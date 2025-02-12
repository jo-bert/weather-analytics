<script setup lang="ts">
defineProps<{
    modelValue: boolean;
    offText: string;
    onText: string;
}>();
const emit = defineEmits(['update:modelValue']);
</script>
<template>
    <div class="mb-4 flex justify-end">
        <label class="inline-flex cursor-pointer items-center">
            <span class="mr-3 text-sm font-medium text-gray-900">{{
                offText
            }}</span>
            <div class="relative">
                <input
                    type="checkbox"
                    :value="modelValue"
                    class="peer sr-only"
                    @input="
                        (event) =>
                            emit(
                                'update:modelValue',
                                (event.target as HTMLInputElement).checked,
                            )
                    "
                    @change="
                        (event) =>
                            emit(
                                'update:modelValue',
                                (event.target as HTMLInputElement).checked,
                            )
                    "
                />
                <div
                    class="peer h-6 w-11 rounded-full bg-gray-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-blue-600 peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300"
                ></div>
            </div>
            <span class="ml-3 text-sm font-medium text-gray-900">{{
                onText
            }}</span>
        </label>
    </div>
</template>
