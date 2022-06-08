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
                <div class="row">
                    <label>{{ keywordLabel }}:</label>
                    <input ref="keyword" class="form-control" :value="keyword">
                </div>
                <div class="row">
                    <label>{{ colorLabel }}:</label>
                    <input ref="color" class="form-control" :value="color">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" v-on:click="success">{{ btnSave }}</button>
                <button class="btn btn-primary" v-on:click="hide">{{ btnCancel }}</button>
            </div>
        </div>
    </div>
</Transition>
</template>

<script>
    import { lang } from '@/lang.js';

    export default {
        data() {
            return {
                showing: false,
                keyword: '',
                color: '',
                index: -1,

                title: lang('Edit Color'),
                keywordLabel: lang('Keyword Label'),
                colorLabel: lang('Color Label'),
                btnSave: lang('Save'),
                btnCancel: lang('Cancel'),
            }
        },
        emits: ['success'],
        methods: {
            show: function (index, keyword, color) {
                this.index = index;
                this.keyword = keyword;
                this.color = color;
                this.showing = true;
            },
            hide: function () {
                this.showing = false;
            },
            success: function () {
                this.$emit('success', {
                    index: this.index,
                    keyword: this.$refs.keyword.value,
                    color: this.$refs.color.value
                });
                this.showing = false;
            }
        }
    }
</script>

<style scoped>
    .modal-body .row label {
        min-width: 80px;
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
