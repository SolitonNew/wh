<script setup>
    import { lang } from '@/lang.js'
    import Spinner from '@/components/Spinner.vue'
    import storage from '@/storage.js';
</script>

<template>
    <div class="toolbar">
        <small class="info">{{ lang('ordering_info') }}</small>
        <select class="form-control" style="width: 65px;margin-right: 5px;" v-on:change="setColumns">
            <option v-for="item in columnList" :value="item.id" :selected="item.id == storage.columns">{{ item.title }}</option>
        </select>
    </div>
    <div class="order-list" v-bind:class="{'fixed-columns-4': columns == 4}" ref="orderingItems">
        <div v-for="item in devices" class="order-item" :data-id="item.data.id">
            <div class="order-item-title">{{ item.control.title }}</div>
        </div>
    </div>
    <Spinner v-if="loading" />
</template>

<script>
    import {api} from '@/api.js';    
    import dragula from 'dragula';

    export default {
        data() {
            return {
                loading: false,
                devices: null,
                columnList: [
                    {id: 3, title: 'x3'},
                    {id: 4, title: 'x4'},
                ],
                columns: 3,
            }
        },
        mounted() {
            this.loading = true;
            this.columns = storage.columns;
            api.get('favorites-order-list', null, (data) => {
                this.devices = data;
                this.initDragula();
                this.loading = false;
            }, (error) => {
                this.loading = false;
            });
        },
        methods: {
            initDragula: function () {
                dragula([this.$refs.orderingItems], {
                    direction: 'horizontal',
                }).on('drop', () => {
                    let ls = this.$refs.orderingItems.children;
                    let ids = new Array();
                    for (let i = 0; i < ls.length; i++) {
                        ids.push(ls[i].getAttribute('data-id'));
                    }

                    api.post('favorites-order-set', {
                        ids: ids.join(','),
                    });
                });
            },
            setColumns: function (event) {
                let columns = event.target.value;
                api.post('favorites-columns-set', {columns: columns}, (data) => {
                    if (data == 'OK') {
                        storage.columns = columns;
                        this.columns = storage.columns;
                    }
                });
            }
        }
    }
</script>

<style scoped>
    .toolbar {
        display: flex;
        width: 100%;
        padding-bottom: 0rem;
        align-items: flex-start;
    }

    .toolbar .info {
        flex-grow: 1;
        display: inline-block;
        margin-bottom: 1.5rem;
        margin-right: 2rem;
    }

    .toolbar .btn {
        height: auto;
    }

    .order-list {
        position: relative;
        display: flex;
        flex-direction: column;
        width: 100%;
        cursor: default;
        touch-action: none;
    }

    .order-item {
        display: inline-block;
        padding: 0px;
        background-color: rgba(0,0,0,0.025);
        border: 1px solid rgba(0,0,0,.125);
        border-radius: 5px;
        margin-bottom: 5px;
    }

    .order-item-title {
        padding: 0.5rem 1rem;
    }

    .gu-mirror {
        position: fixed !important;
        margin: 0 !important;
        z-index: 9999 !important;
        opacity: 0.8;
        cursor: default;
        color: #fff;
        background-color: #007bff;
        border-color: #007bff;
        line-height: 1.5rem;
    }

    .gu-hide {
        display: none !important;
    }

    .gu-unselectable {
        -webkit-user-select: none !important;
        -moz-user-select: none !important;
        -ms-user-select: none !important;
        user-select: none !important;
    }

    .gu-transit {
        opacity: 0;
        -ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity=20)";
        filter: alpha(opacity=20);
    }

    @media(min-width: 669px) {
        .order-list {
            flex-direction: row;
            flex-wrap: wrap;
        }

        .order-item {
            position: relative;
            display: inline-block;
            width: 193px;
            height: 80px;
            margin-right: 5px;
        }

        .fixed-columns-4 .order-item {
            width: 143px;
        }
    }
</style>