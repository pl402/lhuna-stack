<template>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-dark-elevated">
        <div>
            <slot name="logo" />
        </div>

        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-dark-surface shadow-md shadow-black/30 overflow-hidden sm:rounded-lg">
            <slot />
        </div>
    </div>
</template>
