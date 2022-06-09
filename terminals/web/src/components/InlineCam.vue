<template>
    <div class="video-camera" 
        v-bind:class="{play: playing, loading: loading, dummy: port == 0}" 
        v-on:click="onClickPlay">
        <video 
            v-if="port > 0"
            ref="video"
            preload="none" 
            :poster="posterUrl"
            v-on:loadstart="onLoadstart"
            v-on:loadeddata="onLoadedData"
            v-on:error="onError"
            v-on:abort="onAbort"
            v-on:ended="onEnded"></video>
        <div class="video-camera-loading">
            <div class="spinner"></div>
        </div>
        <div class="video-camera-play"></div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                playing: false,
                loading: false,
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
                let play = video.play();
                if (play !== undefined) {
                    play.then(function() {
                        //
                    }).catch(function(error) {
                        //
                    });
                }
            },
            stop: function () {
                let video = this.$refs.video;
                video.pause();
                this.playing = false;
                this.removeSrc();
            },
            removeSrc: function () {
                let video = this.$refs.video;
                if (video.getAttribute('src') != '' && video.getAttribute('src') !== undefined) {
                    video.setAttribute('src', '');
                }
            },
            onClickPlay: function () {
                if (this.port > 0) {
                    if (this.playing) {
                        this.stop();
                    } else {
                        this.start();
                    }
                }
            },
            onLoadstart: function () {
                this.playing = true;
                this.loading = true;
            },
            onLoadedData: function () {
                this.loading = false;
            },
            onError: function () {
                this.playing = false;
                this.loading = false;
                this.removeSrc();
            },
            onAbort: function () {
                this.playing = false;
                this.loading = false;
                this.removeSrc();
            },
            onEnded: function () {
                this.playing = false;
                this.loading = false;
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

    .video-camera-loading {
        position: absolute;
        left: 0px;
        top: 0px;
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: rgba(0,0,0,0);
        opacity: 0;
    }

    .video-camera.loading .video-camera-loading {
        opacity: 0.5;
    }

    .video-camera.dummy {
                
    }

    .video-camera.dummy .video-camera-loading,
    .video-camera.dummy .video-camera-play {
        display: none!important;
    }

    @-webkit-keyframes spinner {
        to {
            -webkit-transform: rotate(360deg);
            transform: rotate(360deg);
        }
    }

    @keyframes spinner {
        to {
            -webkit-transform: rotate(360deg);
            transform: rotate(360deg);
        }
    }

    .video-camera .spinner {
        display: inline-block;
        width: 4rem;
        height: 4rem;
        vertical-align: text-bottom;
        border: 0.25em solid #ffffff;
        border-right-color: transparent;
        border-radius: 50%;
        animation: spinner .75s linear infinite;
    }

    @media(max-width: 668px) {
        .video-camera {
            min-width: 100vw;
            width: 100vw;
            height: calc(100vw / 16 * 9);
        }

        .video-camera.dummy {
            background-color: #cccccc;
        }
    }

    @media(min-width: 669px) {
        .video-camera.dummy {
            display: none;
        }
    }
</style>