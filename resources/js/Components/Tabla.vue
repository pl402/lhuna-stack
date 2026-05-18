<script>
export default {};
</script>

<style>
</style>

<template>
 <div class="w-full overflow-hidden ">
  <div class="w-full overflow-x-auto">
   <table class="w-full whitespace-no-wrap">
    <thead>
     <tr class="
       text-xs
       font-semibold
       tracking-wide
       text-left text-slate-300
       uppercase
       border-b border-dark-border
       bg-dark-elevated/30
      ">
      <slot name="col"></slot>
     </tr>
    </thead>
    <tbody class="bg-dark-surface">
     <slot name="row"></slot>
    </tbody>
   </table>
  </div>
  <slot name="pagination"></slot>
 </div>
</template>
