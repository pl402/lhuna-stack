<script setup>
import { ref, computed } from "vue";
import { usePage } from "@inertiajs/vue3";

const props = defineProps({
  // Size of the glowing orb
  orbSize: {
    type: String,
    default: "w-72 h-72"
  },
  // Blur radius of the glow orb
  orbBlur: {
    type: String,
    default: "blur-[90px]"
  },
  // Opacity of the glow orb on hover
  orbOpacity: {
    type: String,
    default: "group-hover:opacity-10"
  },
  // Allows customizing the wrapper HTML tag if needed
  tag: {
    type: String,
    default: "div"
  },
  // Custom roundness class that respects theme configurations (e.g. rounded-xl)
  rounded: {
    type: String,
    default: "rounded-xl"
  },
  // Custom shadow class that respects theme configurations (e.g. shadow-lg)
  shadow: {
    type: String,
    default: "shadow-lg shadow-black/20"
  }
});

const cardRef = ref(null);
const mouseX = ref("50%");
const mouseY = ref("50%");

const handleMouseMove = (event) => {
  if (!cardRef.value) return;
  const rect = cardRef.value.getBoundingClientRect();
  const x = event.clientX - rect.left;
  const y = event.clientY - rect.top;
  mouseX.value = `${x}px`;
  mouseY.value = `${y}px`;
};

const showOrbs = computed(() => {
  const page = usePage();
  return page.props.themeOrbs !== false;
});
</script>

<template>
  <component
    :is="tag"
    ref="cardRef"
    @mousemove="handleMouseMove"
    :class="[
      'relative overflow-hidden bg-dark-surface border border-dark-border hover:border-brand-500/50 transition-colors duration-300 group',
      rounded,
      shadow
    ]"
    :style="{ '--card-mouse-x': mouseX, '--card-mouse-y': mouseY }"
  >
    <!-- Brand Color Glow (follows cursor inside the panel) -->
    <div
      v-if="showOrbs"
      :class="[
        'absolute bg-brand-500 rounded-full transition-opacity duration-500 pointer-events-none -translate-x-1/2 -translate-y-1/2 opacity-0',
        orbSize,
        orbBlur,
        orbOpacity
      ]"
      :style="{ left: 'var(--card-mouse-x)', top: 'var(--card-mouse-y)' }"
    ></div>

    <!-- Content wrapper to keep it above the internal glow -->
    <div class="relative z-10 h-full w-full">
      <slot />
    </div>
  </component>
</template>
