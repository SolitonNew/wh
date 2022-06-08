<script setup>
    import Spinner from '@/components/Spinner.vue'
    import InlineSwitch from '@/components/InlineSwitch.vue'
    import InlineValue from '@/components/InlineValue.vue'
    import InlineChart from '@/components/InlineChart.vue'
</script>

<template>
<nav>
    <ol>
        <li><router-link to="/">HOME</router-link></li>
        <li style="flex-grow: 1;">{{ title }}</li>
    </ol>
</nav>
<div class="container">
    <div class="device-list">
        <div class="item columns-3" v-for="device in devices">
            <div class="item-header">
                <router-link 
                    class="title"
                    v-if="device.control.typ == 3"
                    :to="{ path: '/device/' + device.data.id }">{{ device.control.title }}</router-link>
                <div v-if="device.control.typ != 3" class="title">{{ device.control.title }}</div>
                <InlineSwitch
                    v-if="device.data.app_control == 1 || device.data.app_control == 3"
                    :ref="'device_' + device.data.id"
                    :id="device.data.id"
                    :value="device.data.value"
                    v-on:changeValue="changeValue" />
                <InlineValue
                    v-if="device.data.app_control != 1 && device.data.app_control != 3"
                    :ref="'device_' + device.data.id"
                    :id="device.data.id"
                    :value="device.data.value * device.control.varStep"
                    :unit="device.control.resolution" />
            </div>
            <div class="item-footer">
                <InlineChart 
                    v-if="device.control.typ == 1"
                    :ref="'chart_' + device.data.id"
                    :id="device.data.id"
                    :color="device.chartColor"
                    :values="device.chartData"
                    :hours="3"
                    />
            </div>
        </div>
    </div>
    <Spinner v-if="loading" />
</div>
</template>

<script>
    import {api} from '@/api.js'

    export default {
        data() {
            return {
                title: '',
                devices: null,
                loading: false,
            }
        },
        mounted() {
            this.emitter.on('deviceChangeValue', this.deviceChangeValue);

            this.loading = true;
            api.get('room/' + this.$route.params.id, null, (data) => {
                this.title = data.title;
                this.devices = data.devices;
                this.loading = false;
            }, (error) => {
                this.loading = false;
            });
        },
        unmounted() {
            this.emitter. off('deviceChangeValue', this.deviceChangeValue);
        },
        methods: {
            deviceChangeValue: function (data) {
                let dev = this.$refs['device_' + data.device_id];
                if (dev && dev.length) {
                    dev[0].setValue(data.value);
                }

                let chart = this.$refs['chart_' + data.device_id];
                if (chart && chart.length) {
                    chart[0].addValue(data.created_at, data.value);
                }
            },
            changeValue: function (data) {
                api.setDeviceValue(data.id, data.value);
            },
        }
    }
</script>

<style scoped>

</style>