<script setup>
    
</script>

<template>
<div class="inline-value">
    <span ref="value" class="inline-value-number">{{ value }}</span>
    <span class="inline-value-unit">{{ unit }}</span>
</div>
</template>

<script>
    export default {
        props: {
            id: Number,
            value: Number,
            unit: String,
        },
        methods: {
            setValue: function (value) {
                this.$refs.value.innerHTML = value;
            },
        }
    }
</script>

<style scoped>
</style>