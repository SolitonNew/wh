import { createApp } from 'vue/dist/vue.esm-bundler.js'
import { createRouter, createWebHistory } from 'vue-router'
import mitt from 'mitt'
import { api } from '@/api.js'

import Login from '@/pages/Login.vue'
import Rooms from '@/pages/Rooms.vue'
import Room from '@/pages/Room.vue'
import Device from '@/pages/Device.vue'
import Favorites from '@/pages/Favorites.vue'
import Settings from '@/pages/Settings.vue'

const router = createRouter({
    history: createWebHistory(),
    routes: [
        { path: '/', component: Rooms },
        { path: '/room/:id', component: Room },
        { path: '/device/:id', component: Device },
        { path: '/favorites', component: Favorites },
        { path: '/settings', component: Settings },
    ]
})

const emitter = mitt();

const app = createApp({
    data() {
        return {
            logined: false,
        }
    },
    created() {
        window.addEventListener('scroll', this.onBodyScroll);
    },
    mounted() {
        api.init(this.apiLoginCallback, this.apiLogoutCallback, this.apiEventCallback);
    },
    destroyed() {
        window.removeEventListener('scroll', this.onBodyScroll);
        api.destroy();
    },
    methods: {
        apiLoginCallback: function (success) {
            this.logined = success;
            if (!success) {
                this.$refs.login.showBadCredentials();
            }
        },
        apiLogoutCallback: function () {
            this.logined = false;
        },
        apiEventCallback: function (event) {
            this.emitter.emit('deviceChangeValue', event);
        },
        onBodyScroll: function (event) {
            if (window.scrollY > 0) {
                document.body.classList.add('nav-offset');
            } else {
                document.body.classList.remove('nav-offset');
            }
        }
    },
})
.use(router);

app.config.globalProperties.emitter = emitter;
app.component('Login', Login);
app.mount('#app');