<script setup>
    
</script>

<template>
    <Transition name="fade">
        <div ref="modalBG" class="modal-bg" v-if="showing">
            <div class="modal-box box-sm border">
                <div class="modal-header">
                    <h3>{{ title }}</h3>
                    <button class="icon" v-on:click="hide">
                        <img src="/img/x-2x.png">
                    </button>
                </div>
                <div class="modal-body">
                    {{ text }}
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" v-on:click="success">{{ btnYes }}</button>
                    <button class="btn btn-primary" v-on:click="hide">{{ btnNo }}</button>
                </div>
            </div>
        </div>
    </Transition>
</template>

<script>
    export default {
        data() {
            return {
                showing: false,
                index: -1,
            }
        },
        props: {
            title: String,
            text: String,
            btnYes: String,
            btnNo: String
        },
        emits: ['success'],
        methods: {
            show: function (index) {
                this.index = index;
                this.showing = true;
            },
            hide: function () {
                this.showing = false;
            },
            success: function () {
                this.$emit('success', {
                    index: this.index,
                });
                this.showing = false;
            }
        }
    }
</script>

<style scoped>
    .modal-body {
        padding-bottom: 1.5rem;
    }

    .fade-enter-active,
    .fade-leave-active {
        transition: opacity 0.5s ease;
    }

    .fade-enter-from,
    .fade-leave-to {
        opacity: 0;
    }
</style>

