<template>
    <Line class="chart"
        ref="chart"
        :id="id"
        :chart-data="chartData"
        :chart-options="options"
        :height="137" 
        />
</template>

<script>
    import moment from 'moment'
    import 'chartjs-adapter-moment';
    import { Line } from 'vue-chartjs'
    import { Chart as ChartJS, LineElement, CategoryScale, PointElement, LinearScale, TimeScale, Filler } from 'chart.js'

    ChartJS.register(LineElement, CategoryScale, PointElement, LinearScale, TimeScale, Filler)

    export default {
        components: { Line },
        data() {
            return {
                chartData: {
                    datasets: [
                        {
                            data: [],
                            backgroundColor: 'rgba(0,0,0,0.125)',
                            borderColor: '#cccccc',
                            fill: true,
                            borderWidth: 2
                        },
                    ]
                },
                options: {
                    responsive: true,
                    scales: {
                        xAxis: {
                            type: 'time',
                            display: true,
                            time: {
                                unit: 'hour',
                                displayFormats: {
                                    hour: 'HH:mm',
                                }
                            },
                            position: 'bottom',
                        }, 
                        yAxis: {
                            ticks: {
                                stepSize: 1.0,
                            }
                        },
                    }
                }
            }
        },
        props: {
            id: Number,
            color: String,
            values: Array,
            hours: Number,
        },
        mounted() {
            if (this.color && this.color.length > 7) {
                let color = this.color.substr(1,this.color.length - 2);
                this.chartData.datasets[0].backgroundColor = color;
                this.chartData.datasets[0].borderColor = color;
            }

            this.setData(this.values);
            this.updateRange();

            this.timer = setInterval(this.updateRange, 60000);
        },
        unmounted() {
            clearInterval(this.timer);
        },
        methods: {
            setData: function (data) {
                this.chartData.datasets[0].data = data ? data : [];
            },
            addValue: function (time, value) {
                if (!this.chartData.datasets[0].data) {
                    this.chartData.datasets[0].data = [];
                }
                this.chartData.datasets[0].data.push({
                    x: time,
                    y: value
                });
                this.updateRange();
            },
            updateRange: function () {
                this.options.scales.xAxis.min = moment().add(-this.hours, 'hours').utc();
                this.options.scales.xAxis.max = moment().utc();

                let start = moment().add(-this.hours, 'hours').utc().unix() * 1000;

                if (this.chartData.datasets[0].data) {
                    let prevVal = false;
                    for (let i = 0; i < this.chartData.datasets[0].data.length; i++) {
                        if (this.chartData.datasets[0].data[0].x < start) {
                            prevVal = this.chartData.datasets[0].data.shift(0);
                        } else {
                            break;
                        }
                    }
                    if (prevVal !== false) {
                        this.chartData.datasets[0].data.unshift(prevVal);
                    }
                }
            }
        }
    }
</script>

<style scoped>
</style>