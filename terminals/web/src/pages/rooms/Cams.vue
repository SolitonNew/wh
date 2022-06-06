<script setup>
    import {api} from '@/api.js'
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
                <video 
                    :data-src="host + ':' + item.stream_port" 
                    preload="none" 
                    :poster="'/img/cams/cam' + (index + 1) + '.png'"
                    v-on:play="onVideoPlay"
                    v-on:ended="onVideoEnd" 
                    v-on:abort="onVideoEnd" 
                    v-on:error="onVideoEnd"></video>
                <div class="video-camera-play" v-on:click="toogleVideo"></div>
            </div>
        </div>
    </div>
</div>
</template>

<script>
    export default {
        data() {
            return {
                data: null,
                loading: true,
                errored: false,
                host: 'http://' + window.location.hostname,
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
            toogleVideo: function (e) {
                let video = e.target.previousSibling;
                if (!video.getAttribute('src')) {
                    let url = video.getAttribute('data-src');
                    video.setAttribute('src', url + '?rnd=' + Math.random());
                    video.play();
                } else {
                    video.setAttribute('src', '');
                }
            },
            onVideoPlay: function (e) {
                e.target.parentElement.classList.add('play');
            },
            onVideoEnd: function (e) {
                e.target.parentElement.classList.remove('play');
                e.target.setAttribute('src', '');
            },

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
        background-color: #ffffff;
    }

    .video-camera video {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .video-camera-title {
        position: absolute; 
        left: 10px;
        top: 10px;
    }

    .video-camera-play {
        position: absolute;
        display: flex;
        align-items: center;
        justify-content: center;
        left: 0px;
        top: 0px;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0);
        cursor: pointer;
        background-image: url('/img/play-circle-8x.png');
        background-repeat: no-repeat;
        background-position: center;
        filter: invert(100%);
        opacity: 0.5;
    }

    .video-camera-play:hover {
        opacity: 0.75;
    }

    .video-camera.play .video-camera-play {
        opacity: 0;
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