<script setup>

const props = defineProps({
  href: String,
  target: String,
  active: Boolean,
});

</script>

<template>
  <a :href="href" :target="target" class="
      inline-flex
      items-center
      px-4
      py-2
      bg-brand-500
      border border-transparent
      rounded-md
      font-semibold
      text-xs text-white
      uppercase
      tracking-widest
      hover:bg-brand-400
      active:bg-brand-600
      focus:outline-none focus:border-brand-500 focus:ring focus:ring-brand-500
      disabled:opacity-25
      shadow-md shadow-brand-500/20
      hover:shadow-lg shadow-brand-500/30
      focus:shadow-xl shadow-brand-500/40
      active:ring-2 active:ring-brand-500
      transition
    ">
    <slot />
  </a>
</template>
