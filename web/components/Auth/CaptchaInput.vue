<template>
    <ClientOnly>
        <div class="mb-10">
            <div style="height: 100px" id="captcha-container" class="smart-captcha "></div>
            <p class="text-sub-subtitle text-error" v-if="!!error">{{ error }}</p>
        </div>
    </ClientOnly>
</template>
<script setup lang="ts">
import { onMounted, nextTick } from "#imports";

const sitekey = "ysc1_1VuBx5qmmijsrjugUeElBYOu7LR1jToqzlu3yUVS25d29a99"

const params = {
    sitekey,
    test: false
}

const emit = defineEmits(['update:modelValue'])

defineProps({
    error: String,
})

onMounted(() => {
    nextTick(() => {
        const w = window as any
        if (w.smartCaptcha) {
            const container = document.getElementById('captcha-container');
            const widgetId = w.smartCaptcha.render(container, params);
            console.log("container", params, w.smartCaptcha)
            const unsubscribe = w.smartCaptcha.subscribe(
                widgetId,
                'success',
                (token: string) => emit('update:modelValue', token)
            );
        }
    })

})
</script>
<style></style>