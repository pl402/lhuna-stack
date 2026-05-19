<script>
export default {};
</script>

<style>
.table-row-enter-active,
.table-row-leave-active {
  transition: all 0.3s ease;
}

.table-row-enter-from,
.table-row-leave-to {
  opacity: 0;
  transform: translateY(6px);
}

.table-row-move {
  transition: transform 0.3s ease;
}
</style>

<template>
 <div class="w-full overflow-hidden">
  <div class="relative w-full">
    <!-- Top scroll fade indicator (placed below the sticky header) -->
    <div class="absolute top-[37px] left-0 right-0 h-6 bg-gradient-to-b from-dark-surface to-transparent pointer-events-none z-10"></div>
    
    <!-- Bottom scroll fade indicator -->
    <div class="absolute bottom-0 left-0 right-0 h-6 bg-gradient-to-t from-dark-surface to-transparent pointer-events-none z-10"></div>

    <div class="w-full max-h-96 overflow-y-auto overflow-x-auto custom-scrollbar">
     <table class="w-full whitespace-no-wrap">
      <thead class="sticky top-0 z-20 bg-dark-surface backdrop-blur-md">
       <tr class="
         text-xs
         font-semibold
         tracking-wide
         text-left text-slate-300
         uppercase
         border-b border-dark-border
         bg-dark-elevated/20
        ">
        <slot name="col"></slot>
       </tr>
      </thead>
      <TransitionGroup tag="tbody" name="table-row" class="bg-transparent">
       <slot name="row"></slot>
      </TransitionGroup>
     </table>
    </div>
  </div>
  <slot name="pagination"></slot>
 </div>
</template>
