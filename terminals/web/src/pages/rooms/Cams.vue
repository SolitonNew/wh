<script setup>
</script>

<template>
<div class="video-cameras-block" v-if="data && data.length > 0">
    <div class="video-cameras-title">VIDEO CAMERAS</div>
    <div ref="scroller" class="video-cameras-scroller" 
        v-on:scroll="onScroll" 
        v-on:touchstart="onTouchStart" 
        v-on:touchend="onTouchEnd">
        <div class="video-cameras-list">
            <div v-for="(item, index) in data" class="video-camera">
                <img :src="'/img/cams/cam' + (index + 1) + '.png'">
            </div>
        </div>
    </div>
</div>
</template>

<script>
    import {api} from '@/api.js'

    export default {
        data() {
            return {
                data: null,
                loading: true,
                errored: false,
            }
        },
        scrollTimer: false,
        scrollStart: true,
        scrollAnimate: false,
        scrollAnimateTo: false,
        created() {
            this.scrollAnimate = setInterval(this.onScrollAnimate, 10);
        },
        mounted() {
            api.get('cams', null, (data) => {
                this.data = data;
            });

            window.addEventListener("resize", this.onResizeWindow);
        },
        unmounted() {
            window.removeEventListener("resize", this.onResizeWindow);
        },
        destroyed() {
            clearInterval(this.scrollAnimate);
        },
        methods: {
            onScroll: function (event) {
                this.scrollComplette();
            },
            onTouchStart: function (event) {
                this.scrollAnimateTo = false;
                this.scrollStart = true;
            },
            onTouchEnd: function (event) {
                this.scrollStart = false;
                this.scrollComplette();
            },
            scrollComplette: function () {
                if (this.scrollStart) return;
                if (window.innerWidth > 668) return;

                clearTimeout(this.scrollTimer);
                this.scrollTimer = setTimeout(() => {
                    let scroller = this.$refs.scroller;
                    let w = scroller.offsetWidth;
                    let x = scroller.scrollLeft;
                    this.scrollAnimateTo = w * Math.round(x / w);
                }, 50);
            },
            onResizeWindow: function () {
                if (window.innerWidth > 668) return ;

                this.scrollAnimateTo = false;
                let scroller = this.$refs.scroller;
                let w = scroller.offsetWidth;
                let x = scroller.scrollLeft;
                scroller.scrollLeft = w * Math.round(x / w);
            },
            onScrollAnimate: function () {
                if (this.scrollAnimateTo === false) return ;
                if (this.scrollStart) return;
                if (window.innerWidth > 668) return;

                const step = 25;
                let scroller = this.$refs.scroller;
                if (scroller === undefined) return ;
                let x = scroller.scrollLeft;
                if (Math.abs(x - this.scrollAnimateTo) > step) {
                    if (x > this.scrollAnimateTo) {
                        scroller.scrollLeft -= step;
                    } else {
                        scroller.scrollLeft += step;
                    }
                } else {
                    scroller.scrollLeft = this.scrollAnimateTo;
                    this.scrollAnimateTo = false;
                }
            }
        }
    }
</script>

<style scoped>
    .video-cameras-block {
        margin-bottom: 1rem;
    }

    .video-cameras-title {
        color: #818182;
        padding: 0.75rem 1.25rem;
    }

    .video-cameras-scroller {
        display: flex;
        width: 100%;
        overflow-x: auto;
        overflow-y: hidden;
        justify-content: center;
    }

    .video-cameras-list {
        display: flex;
        flex-direction: row;
        flex-wrap: nowrap;
    }

    .video-camera {
        position: relative;
        display: inline-block;
        min-width: 420px;
        height: calc(420px / 16 * 9);
        background-color: rgba(0,0,0,0.5);
    }

    .video-camera img {
        width: 100%;
        height: 100%;
    }

    .video-camera-title {
        position: absolute; 
        left: 10px;
        top: 10px;
    }

    @media(max-width: 668px) {
        .video-camera {
            min-width: 100vw;
            width: 100vw;
            height: calc(100vw / 16 * 9);
        }

        .video-cameras-scroller {
            justify-content: flex-start;
        }

        .video-cameras-scroller::-webkit-scrollbar {
            width: 0px;
            height: 0px;
            border: none;
        }
    }
</style>