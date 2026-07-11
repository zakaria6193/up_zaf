<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

type Props = {
    className?: HTMLAttributes['class'];
};

defineProps<Props>();
</script>

<template>
    <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 100 100"
        :class="className"
        v-bind="$attrs"
    >
        <text
            x="50"
            y="70"
            font-family="system-ui, -apple-system, sans-serif"
            font-size="48"
            font-weight="bold"
            text-anchor="middle"
            fill="currentColor"
        >
            U1
        </text>
    </svg>
</template>
