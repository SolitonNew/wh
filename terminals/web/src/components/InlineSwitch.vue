<script setup>
    
</script>

<template>
<div class="inline-switch">
    <input type="checkbox" class="inline-switch-input"
        ref="checker"
        :id="'device_' + id" 
        :checked="value"
        v-on:input="input">
    <label class="inline-switch-label" 
        :for="'device_' + id"></label>
</div>
</template>

<script>
    export default {
        props: {
            id: Number,
            value: Number,
        },
        emits: [
            'changeValue'
        ],
        methods: {
            input: function (event) {
                this.$emit('changeValue', {
                    id: this.id, 
                    value: event.target.checked ? 1 : 0
                });
            },
            setValue: function (value) {
                this.$refs.checker.checked = (value === 1);
            },
        }
    }
</script>

<style scoped>

</style>