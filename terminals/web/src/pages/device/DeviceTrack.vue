<template>
    <div class="device-value">
        <div class="device-value-row">
            <div class="device-value-unit hiddend">{{ unitValue }}</div>
            <div class="device-value-number">{{ displayValue }}</div>
            <div class="device-value-unit">{{ unitValue }}</div>
        </div>
    </div>
    <div class="device-min-max" >
        <div>{{ minValue }}{{ unitValue }}</div>
        <div>{{ maxValue }}{{ unitValue }}</div>
    </div>
    <input ref="trackbar" type="range" 
        v-on:input="trackbarInput" 
        v-on:change="trackbarChange" />
</template>

<script>
    export default {
        data() {
            return {
                displayValue: 0,
            }
        },
        props: {
            value: Number,
            minValue: Number,
            maxValue: Number,
            stepValue: Number,
            unitValue: String,
        },
        mounted() {
            this.displayValue = this.value * this.stepValue;
            this.$refs.trackbar.min = this.minValue;
            this.$refs.trackbar.max = this.maxValue;
            this.$refs.trackbar.value = this.displayValue;
            this.$refs.trackbar.step = this.stepValue;
        },
        emits: ['changeValue'],
        methods: {
            trackbarInput: function(event) {
                this.displayValue = event.target.value;
            },
            trackbarChange: function(event) {
                this.$emit('changeValue', {
                    value: event.target.value / this.stepValue
                });
            }
        }
    }
</script>

<style scoped>
    .device-value {
        flex-grow: 1;
        display: flex;
        align-items: center;
    }

    .device-value-row {
        display: flex;
        align-items: baseline;
        padding: 2rem 0px;
        -webkit-user-select: none;
    }

    .device-value-number {
        color: #007bff;
        font-size: 9rem;
    }

    .device-value-unit {
        font-size: 2rem;
    }

    .device-value-unit.hiddend {
        color: transparent;
    }

    .device-min-max {
        display: flex;
        width: 100%;
    }

    .device-min-max :first-child {
        flex-grow: 1;
    }
</style>