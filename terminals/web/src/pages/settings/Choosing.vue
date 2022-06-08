<script setup>
    import { lang } from '@/lang.js'
    import Spinner from '@/components/Spinner.vue'
    import InlineSwitch from '@/components/InlineSwitch.vue';
</script>

<template>
    <small class="info">{{ lang('choosing_info') }}</small>
    <div class="toolbar">
        <select ref="filter" class="form-control" v-on:change="changeFilterValue">
            <option v-for="(item, key) in types" :value="key" :selected="key == filterValue">{{ item['title'] }}</option>
        </select>
    </div>
    <div v-for="item in devices" class="checked-item" v-show="filterValue == 0 || item.data.app_control == filterValue">
        <div class="checked-item-label">{{ item.control.title }}</div>
        <InlineSwitch 
            :id="item.data.id" 
            :value="isChecked(item.data.id)"
            v-on:changeValue="checkDevice" />
    </div>
    <Spinner v-if="loading" />
</template>

<script>
    import {api} from '@/api.js';
    import storage from '@/storage.js';

    export default {
        data() {
            return {
                loading: false,
                devices: null,
                checkeds: [],
                types: null,
                filterValue: 0,
            }
        },
        mounted() {
            this.filterValue = storage.settings.checking.filter;
            this.types = storage.app_controls;

            this.loading = true;
            api.get('favorites-device-list', null, (data) => {
                this.checkeds = data.checkeds;
                this.devices = data.devices;
                this.loading = false;
            }, (error) => {
                this.loading = false;
            });
        },
        methods: {
            isChecked: function (id) {
                if (this.checkeds.indexOf(id.toString()) === -1) {
                    return 0;
                } else {
                    return 1;
                }
            },
            checkDevice: function (data) {
                if (data.value == 1) {
                    api.post('favorites-device-add/' + data.id, null);
                } else {
                    api.post('favorites-device-del/' + data.id, null);
                }
            },
            changeFilterValue: function (event) {
                this.filterValue = parseInt(event.target.value);
                storage.settings.checking.filter = this.filterValue;
            }
        }
    }
</script>

<style scoped>
    .info {
        display: inline-block;
        margin-bottom: 1.5rem;
    }

    .toolbar {
        padding-bottom: 2rem;
    }

    .checked-item {
        display: flex;
        padding: 0.5rem 0.75rem;
        border-bottom: 1px solid rgba(0,0,0,.125);
    }

    .checked-item:last-child {
        border-bottom: none;
    }

    .checked-item-label {
        flex-grow: 1;
    }
</style>