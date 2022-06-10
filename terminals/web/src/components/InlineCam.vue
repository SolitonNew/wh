<template>
    <div class="video-camera" 
        v-bind:class="{play: playing, loading: loading, empty: poster == '', dummy: video == undefined}" 
        v-on:click="onClickPlay">
        <video 
            v-if="video != ''"
            ref="video"
            preload="none" 
            :poster="poster ? (poster + '&rnd=' + rnd) : ''"
            v-on:loadstart="onLoadstart"
            v-on:loadeddata="onLoadedData"
            v-on:error="onError"
            v-on:abort="onAbort"
            v-on:ended="onEnded"></video>
        <div class="video-camera-empty"></div>
        <div class="video-camera-loading">
            <div class="spinner"></div>
        </div>
        <div class="video-camera-play"></div>
        <div class="video-camera-fullscreen" v-on:click="fullscreen"></div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                playing: false,
                loading: false,
                rnd: Math.random(),
            }
        },
        props: {
            poster: String,
            video: String,
        },
        posterTimer: false,
        mounted() {
            this.posterTimer = setInterval(() => {
                this.rnd = Math.random();
            }, 60000);
        },
        unmounted() {
            clearInterval(this.posterTimer);
        },
        methods: {
            start: function () {
                let video = this.$refs.video;
                video.setAttribute('src', this.video);
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
                if (this.video) {
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
            },
            fullscreen: function (e) {
                e.stopPropagation();
                this.$refs.video.requestFullscreen();
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

    .video-camera.empty .video-camera-empty {
        position: absolute;
        display: inline-block;
        left: 0px;
        top: 0px;
        width: calc(100% - 1rem);
        height: 100%;
        background-color: rgba(0,0,0,0.35);
    }

    .video-camera-fullscreen {
        position: absolute;
        display: inline-block;
        width: 50px;
        height: 50px;
        right: 1rem;
        bottom: 1rem;
        border-radius: 25px;
        margin-right: 1rem;
        cursor: pointer;
        background-color: rgba(110,110,110,0.65);
        background-image: url('/img/zoom-in-3x.png');
        background-repeat: no-repeat;
        background-position: center;
        filter: invert(100%);
        opacity: 0.65;
    }

    .video-camera-fullscreen:hover {
        opacity: 1;
    }

    .video-camera.dummy .video-camera-fullscreen {
        display: none;
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

        .video-camera-empty {
            width: 100%!important;
        }

        .video-camera-fullscreen {
            right: 0px;
        }
    }

    @media(min-width: 669px) {
        .video-camera {
            padding-left: 1rem;
        }

        .video-camera.dummy {
            display: none;
        }
    }
</style>