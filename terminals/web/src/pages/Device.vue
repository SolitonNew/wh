<script setup>
    import { lang } from '@/lang.js';
    import Spinner from '@/components/Spinner.vue'
    import DeviceTrack from '@/pages/device/DeviceTrack.vue';
</script>

<template>
<nav>
    <ol>
        <li><router-link to="/">{{ lang('Home') }}</router-link></li>
        <li><router-link :to="'/room/' + roomID">{{ roomTitle }}</router-link></li>
        <li style="flex-grow: 1;">{{ deviceTitle }}</li>
    </ol>
</nav>
<div class="container center">
    <div v-show="!loading" id="device" class="box-md border device-box">
        <DeviceTrack 
            v-if="deviceControlTyp == 3"
            :value="value"
            :minValue="minValue"
            :maxValue="maxValue"
            :stepValue="stepValue"
            :unitValue="unitValue" 
            v-on:changeValue="deviceChangeValue"/>
    </div>
    <Spinner v-if="loading" />
</div>
</template>

<script>
    import {api} from '@/api.js'

    export default {
        data() {
            return {
                loading: false,
                roomID: null,
                roomTitle: '',
                deviceID: null,
                deviceControlTyp: 0,
                deviceTitle: '',

                value: 0,
                minValue: 0,
                maxValue: 100,
                stepValue: 1,
                unitValue: '',
            }
        },
        mounted() {
            this.deviceID = this.$route.params.id;

            this.loading = true;
            api.get('device/' + this.$route.params.id, null, (data) => {
                this.roomID = data.room.id;
                this.roomTitle = data.room.title;
                this.deviceTitle = data.device.title;
                this.deviceControlTyp = data.device.control.typ;
                
                this.value = data.device.value;
                this.unitValue = data.device.control.resolution;
                this.minValue = data.device.control.varMin;
                this.maxValue = data.device.control.varMax;
                this.stepValue = data.device.control.varStep;
                
                this.loading = false;
            }, (error) => {
                this.loading = false;
            });
        },
        methods: {
            deviceChangeValue: function (data) {
                api.setDeviceValue(this.deviceID, data.value);
            }
        }
    }
</script>

<style scoped>
    #device {
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    @media(max-width: 690px) {
        #device {
            height: calc(100vh - 9rem);
        }

        .container {
            padding-bottom: 0px;
        }

        .device-box {
            display: flex;
        }
    }
</style>


