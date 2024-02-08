<script setup>
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import { Link } from "@inertiajs/vue3";
</script>

<template>
  <div
    class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100"
  >
    <div>
      <Link href="/">
        <!-- <ApplicationLogo class="fill-current text-gray-500" /> -->

        <img class="w-20 h-20" src="../../../public/image/BElogo.png" />
      </Link>
    </div>

    <div
      class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg"
    >
      <slot />
    </div>
    <div class="absolute bottom-0 left-2 w-fit">
      <p class="text-xs text-slate-500">Version 01.20231107.a</p>
    </div>
  </div>
</template>
