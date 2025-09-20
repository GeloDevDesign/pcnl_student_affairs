<script setup>
import { ref } from "vue";
</script>

<template>
  <div class="join flex justify-end w-auto">
    <input
      class="input join-item rounded-l-full bg-white lg:input-md md:input-md input-sm"
      placeholder="Search..."
    />
    <button
      class="btn btn-primary lg:btn-md md:btn-md btn-sm  join-item rounded-r-full"
    >
      Search
    </button>
  </div>
</template>
