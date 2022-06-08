<script setup>
    import { lang } from '@/lang.js';
    import Choosing from '@/pages/settings/Choosing.vue';
    import Ordering from '@/pages/settings/Ordering.vue';
    import Coloring from '@/pages/settings/Coloring.vue';
</script>

<template>
<nav>
    <ol>
        <li><router-link to="/">{{ lang('Home') }}</router-link></li>
        <li><router-link to="/favorites">{{ lang('Favorites') }}</router-link></li>
        <li style="flex-grow: 1;">{{ lang('Settings') }}</li>
    </ol>
</nav>
<div class="container center">
    <div class="tabs">
        <button v-on:click="showPage(1)" class="btn" :class="{active: page == 1}">{{ lang('Choosing') }}</button>
        <button v-on:click="showPage(2)" class="btn" :class="{active: page == 2}">{{ lang('Ordering') }}</button>
        <button v-on:click="showPage(3)" class="btn" :class="{active: page == 3}">{{ lang('Coloring') }}</button>
    </div>
    <div class="box-md border">
        <Choosing v-if="page == 1" />
        <Ordering v-if="page == 2" />
        <Coloring v-if="page == 3" />
    </div>
</div>
</template>

<script>
    import storage from '@/storage.js';

    export default {
        data() {    
            return {
                page: 1,
                pageOneFilter: 0,
            }
        },
        mounted() {
            this.page = storage.settings.page;
        },
        methods: {
            showPage: function (index) {
                this.page = index;
                storage.settings.page = index;
            }
        }
    }
</script>

<style scoped>
    .box-md {
        margin-bottom: 1rem;
    }
    
    .tabs {
        padding-bottom: 0.75rem;
    }

    .tabs .btn.active {
        font-weight: bold;
    }
</style>

