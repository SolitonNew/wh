<script setup>
    
</script>

<template>
    <div class="inline-value">
        <span class="inline-value-number">{{ displayValue }}</span>
        <span class="inline-value-unit">{{ unit }}</span>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                displayValue: 0,
            }
        },
        props: {
            id: Number,
            value: Number,
            unit: String,
        },
        mounted() {
            this.displayValue = this.makeValue(this.value);
        },
        methods: {
            setValue: function (value) {
                this.displayValue = this.makeValue(value);
            },
            makeValue: function (value) {
                return Math.round(value * 1000) / 1000;
            }
        }
    }
</script>

<style scoped>
</style>