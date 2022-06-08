<script setup>
    import Spinner from '@/components/Spinner.vue'
    import ColorDialog from '@/pages/settings/ColorDialog.vue';
    import ColorDelete from '@/pages/settings/ColorDelete.vue';
</script>

<template>
    <div class="toolbar">
        <button class="btn btn-primary" v-on:click="dialogEdit(-1)">ADD</button>
    </div>
    <div>
        <div v-for="(item, index) in colors" class="item-row">
            <div class="item-value">
                <div class="item-keyword">{{ item.keyword }}</div>
                <div class="item-color">{{ item.color }}</div>
            </div>
            <div class="item-actions">
                <button class="icon" v-on:click="dialogEdit(index)">
                    <img src="/img/pencil-2x.png">
                </button>
                <button class="icon" v-on:click="deleteShow(index)">
                    <img src="/img/x-2x.png">
                </button>
            </div>
        </div>
    </div>
    <ColorDialog ref="dialog" v-on:success="dialogSuccess" />
    <ColorDelete ref="delete" v-on:success="deleteSuccess" text="Are you sure?" />
    <Spinner v-if="loading" />
</template>

<script>
    import {api} from '@/api.js';

    export default {
        data() {
            return {
                loading: false,
                colors: null,
            }
        },
        mounted() {
            this.loading = true;
            api.get('device-color-list', null, (data) => {
                this.colors = data;
                this.loading = false;
            }, (error) => {
                this.loading = false;
            });
        },
        methods: {
            dialogEdit: function (index) {
                if (this.colors[index]) {
                    this.$refs.dialog.show(index, this.colors[index].keyword, this.colors[index].color);
                } else {
                    this.$refs.dialog.show(-1, '', '');
                }
            },
            dialogSuccess: function (data) {
                let index = data.index;
                let keyword = data.keyword;
                let color = data.color;

                api.post('set-device-color/' + index, {
                    keyword: keyword,
                    color: color,
                }, (data) => {
                    if (this.colors[index]) {
                        this.colors[index].keyword = keyword;
                        this.colors[index].color = color;
                    } else {
                        this.colors.push({
                            keyword: keyword,
                            color: color,
                        });
                    }
                });
            },
            deleteShow: function (index) {
                if (this.colors[index]) {
                    this.$refs.delete.show(index);
                }
            },
            deleteSuccess: function (data) {
                let index = data.index;
                if (this.colors[index]) {
                    api.delete('del-device-color/' + index, null, (data) => {
                        this.colors.splice(index, 1);
                    });
                }
            }
        }
    }
</script>

<style scoped>
    .toolbar {
        padding-bottom: 2rem;
    }

    .item-row {
        display: flex;
        align-items: center;
        padding-bottom: 0.25rem;
        margin-bottom: 0.25rem;
        border-bottom: 1px solid rgba(0,0,0,.125);
    }

    .item-row:last-child {
        border-bottom: none;
        padding-bottom: 0rem;
        margin-bottom: 0rem;
    }

    .item-value {
        flex-grow: 1;
        display: flex;
    }

    .item-keyword {
        flex-grow: 1;
    }

    .item-color {
        flex-grow: 1;
    }

    .item-actions {
        display: flex;
    }

    .item-actions > button {
        margin-right: 10px;
    }

    .item-actions > button:last-child {
        margin-right: 0px;
    }

    @media(max-width: 512px) {
        .item-value {
            flex-direction: column;
        }
    }
</style>