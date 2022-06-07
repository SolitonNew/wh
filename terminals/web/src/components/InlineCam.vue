
<template>
<div class="video-camera" v-bind:class="{play: isPlaying}">
    <video 
        ref="video"
        preload="none" 
        :poster="posterUrl"
        v-on:loadstart="onLoadstart"
        v-on:error="onError"
        v-on:abort="onAbort"
        v-on:ended="onEnded"></video>
    <div class="video-camera-play" v-on:click="onClickPlay"></div>
</div>
</template>

<script>
    export default {
        data() {
            return {
                isPlaying: false,
            }
        },
        props: {
            port: Number,
        },
        computed: {
            posterUrl() {
                return '/img/cams/cam' + (this.port - 9999) + '.png';
            },
        },
        mounted() {
            //
        },
        unmounted() {
            //
        },
        methods: {
            start: function () {
                let video = this.$refs.video;
                video.setAttribute('src', 'http://' + window.location.hostname + ':' + this.port);
                video.play();
            },
            stop: function () {
                video.pause();
                this.isPlaying = false;
                this.removeSrc();
            },
            removeSrc: function () {
                let video = this.$refs.video;
                if (video.getAttribute('src') != '' && video.getAttribute('src') !== undefined) {
                    video.removeAttribute('src');
                }
            },
            onClickPlay: function () {
                if (this.isPlaing) {
                    this.stop();
                } else {
                    this.start();
                }
            },
            onLoadstart: function () {
                this.isPlaying = true;
            },
            onError: function () {
                this.isPlaying = false;
                this.removeSrc();
            },
            onAbort: function () {
                this.isPlaying = false;
                this.removeSrc();
            },
            onEnded: function () {
                this.isPlaying = false;
                this.removeSrc();
            }
        }
    }
</script>

<style scoped>
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
    }
</style>