<script setup>
    import Cams from '@/pages/rooms/Cams.vue'
    import InlineValue from '@/components/InlineValue.vue'
    import InlineSwitch from '@/components/InlineSwitch.vue'
</script>

<template>
<nav>
    <ol>
        <li style="flex-grow: 1;">HOME</li>
        <li class="right"><router-link to="/favorites">FAVORITES</router-link></li>
    </ol>
</nav>
<div class="container">
    <div class="room-groups">
        <div :class="'columns-' + columns" v-for="group in data">
            <div class="room-group">
                <div class="group-title">{{ group.title }}</div>
                <div class="item" v-for="room in group.rooms">
                    <router-link :to="{ path: '/room/' + room.id }" class="title">{{ room.title }}</router-link>
                    <InlineValue 
                        v-if="room.temperature"
                        :ref="'device_' + room.temperature.id"
                        :id="room.temperature.id" 
                        :value="room.temperature.value"
                        unit="°C" />
                    <InlineSwitch 
                        v-if="room.switch_1" 
                        :ref="'device_' + room.switch_1.id"
                        :id="room.switch_1.id" 
                        :value="room.switch_1.value"
                        v-on:changeValue="changeValue" />
                    <InlineSwitch 
                        v-if="room.switch_2" 
                        :ref="'device_' + room.switch_2.id"
                        :id="room.switch_2.id" 
                        :value="room.switch_2.value"
                        v-on:changeValue="changeValue" />
                </div>
            </div>
        </div>
    </div>
    <Cams v-show="data !== null" />
</div>
</template>

<script>
    import {api} from '@/api.js'

    export default {
        data() {
            return {
                columns: 3,
                data: null,
            }
        },
        mounted() {
            this.emitter.on('deviceChangeValue', this.deviceChangeValue);
        
            api.get('rooms', null, (data) => {
                this.columns = data.length;
                if (this.columns < 1) this.columns = 1;
                if (this.columns > 4) this.columns = 4;
                this.data = data;
            }, (error) => {
                //
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
            },
            changeValue: function (data) {
                api.setDeviceValue(data.id, data.value);
            }
        }
    }
</script>

<style scoped>

</style>