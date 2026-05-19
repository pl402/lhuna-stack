<script setup>
import Modal from "./Modal.vue";

const emit = defineEmits(["close"]);

defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    maxWidth: {
        type: String,
        default: "2xl",
    },
    closeable: {
        type: Boolean,
        default: true,
    },
    padding: {
        type: String,
        default: "px-4 py-3",
    },
    paddingContent: {
        type: String,
        default: "p-4",
    },
});

const close = () => {
    emit("close");
};
</script>

<template>
    <Modal
        :show="show"
        :max-width="maxWidth"
        :closeable="closeable"
        @close="close"
    >
        <div>
            <div
                class="font-semibold text-xl text-slate-200 leading-tight border-dark-border border-b-2 border-b-solid bg-dark-elevated/50 rounded-t-[inherit] sticky -top-16 z-50"
                :class="padding"
            >
                <slot name="title" />
            </div>

            <div class="w-full" :class="paddingContent">
                <slot name="content" />
            </div>
        </div>

        <div
            class="flex flex-row justify-end px-4 py-2 border-dark-border border-t-2 border-t-solid bg-dark-elevated/50 rounded-b-[inherit]"
        >
            <slot name="footer" />
        </div>
    </Modal>
</template>
