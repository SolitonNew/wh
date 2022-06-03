<script setup>
</script>

<template>
    <div class="order-list" ref="orderingItems">
        <div v-for="item in devices" class="order-item" :data-id="item.data.id">
            <div class="order-item-title">{{ item.control.title }}</div>
        </div>
    </div>
</template>

<script>
    import {api} from '@/api.js';    
    import dragula from 'dragula';

    export default {
        data() {
            return {
                devices: null,
            }
        },
        mounted() {
            api.get('favorites-order-list', null, (data) => {
                this.devices = data;

                this.initDragula();
            });
        },
        methods: {
            initDragula: function () {
                dragula([this.$refs.orderingItems, this.$refs.orderingItems], {
                    accepts: () => {
                        return true;
                    },
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
        }
    }
</script>

<style scoped>
    .order-list {
        cursor: default;
        touch-action: none;
    }

    .order-item {
        padding: 0px;
        border: 1px solid rgba(0,0,0,.125);
        background-color: rgba(0,0,0,0.025);
        border-radius: 5px;
        margin-bottom: 4px;
    }

    .order-item-title {
        padding: 0.5rem 1rem;
    }

    .gu-mirror {
        position: fixed !important;
        margin: 0 !important;
        z-index: 9999 !important;
        opacity: 0.8;
        -ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity=80)";
        filter: alpha(opacity=80);
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
        opacity: 0.2;
        -ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity=20)";
        filter: alpha(opacity=20);
    }
</style>