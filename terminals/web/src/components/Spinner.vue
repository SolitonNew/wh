<script setup>
    
</script>

<template>
<div class="spinner-bg">
    <div class="spinner"></div>
</div>
</template>

<script>
    export default {
        
    }
</script>

<style scoped>
    .spinner-bg {
        position: fixed;
        display: flex;
        align-items: center;
        justify-content: center;
        left: 0px;
        top: 0px;
        width: 100%;
        height: 100%;
        background-color: rgba(255, 255, 255, 0.5);
        z-index: 1100;
    }

    @-webkit-keyframes spinner {
        to {
            -webkit-transform: rotate(360deg);
            transform: rotate(360deg);
        }
    }

    @keyframes spinner {
        to {
            -webkit-transform: rotate(360deg);
            transform: rotate(360deg);
        }
    }

    .spinner {
        display: inline-block;
        width: 4rem;
        height: 4rem;
        vertical-align: text-bottom;
        border: 0.25em solid #007bff;
        border-right-color: transparent;
        border-radius: 50%;
        animation: spinner .75s linear infinite;
    }
</style>