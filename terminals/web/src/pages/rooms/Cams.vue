<script setup>
</script>

<template>
<div class="video-cameras" v-if="data && data.length > 0">
    <div class="title">VIDEO CAMERAS</div>
</div>
</template>

<script>
    import {api} from '@/api.js'

    export default {
        data() {
            return {
                data: null,
                loading: true,
                errored: false,
            }
        },
        mounted() {
            api.get('cams', null, (data) => {
                this.data = data;
            });
        }
    }
</script>

<style scoped>
</style>