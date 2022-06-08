import { createApp } from 'vue/dist/vue.esm-bundler.js'
import { createRouter, createMemoryHistory } from 'vue-router'
import mitt from 'mitt'
import { api } from '@/api.js'
import { setLangData } from '@/lang.js'
import storage from '@/storage.js'

import Spinner from '@/components/Spinner.vue'
import Login from '@/pages/Login.vue'
import Rooms from '@/pages/Rooms.vue'
import Room from '@/pages/Room.vue'
import Device from '@/pages/Device.vue'
import Favorites from '@/pages/Favorites.vue'
import Settings from '@/pages/Settings.vue'

const router = createRouter({
    history: createMemoryHistory(),
    routes: [
        { path: '/', component: Rooms },
        { path: '/room/:id', component: Room },
        { path: '/device/:id', component: Device },
        { path: '/favorites', component: Favorites },
        { path: '/settings', component: Settings },
    ],
});

router.afterEach((to, from) => {
    window.dispatchEvent(new Event('resize'));
});

const emitter = mitt();

const app = createApp({
    pageScrollTimer: false,
    pageScrollStart: true,
    pageScrollAnimate: false,
    pageScrollAnimateTo: false,
    data() {
        return {
            started: false,
            logined: false,
        }
    },
    computed: {
        checkPageScrollLeft() {
            let path = router.currentRoute.value.path;
            return path !== '/';
        },
        checkPageScrollRight() {
            let path = router.currentRoute.value.path;
            return path !== '/favorites' && path !== '/settings';
        },
    },
    created() {
        window.addEventListener('scroll', this.onBodyScroll);
        window.addEventListener("resize", this.onResizeWindow);
        this.pageScrollAnimate = setInterval(this.onPageScrollAnimate, 10);
    },
    mounted() {
        api.init(this.apiLoginCallback, this.apiLogoutCallback, this.apiEventCallback);
        api.get('start', null, (data) => {
            setLangData(data.lang);
            storage.app_controls = data.app_controls;
            this.started = true;
        }, (error) => {
            
        });
    },
    destroyed() {
        clearInterval(this.pageScrollAnimate);
        window.removeEventListener("resize", this.onResizeWindow);
        window.removeEventListener('scroll', this.onBodyScroll);
        api.destroy();
    },
    methods: {
        apiLoginCallback: function (success) {
            this.logined = success;
            if (success) {
                setTimeout(() => {
                    this.onResizeWindow();
                }, 1);
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
        },
        
        onPageScroll: function (event) {
            this.pageScrollComplette();
        },
        onPageTouchStart: function (event) {
            this.pageScrollAnimateTo = false;
            this.pageScrollStart = true;
        },
        onPageTouchEnd: function (event) {
            this.pageScrollStart = false;
            this.pageScrollComplette();
        },
        pageScrollComplette: function () {
            if (this.pageScrollStart) return;
            if (window.innerWidth > 668) return;

            clearTimeout(this.pageScrollTimer);
            this.pageScrollTimer = setTimeout(() => {
                let scroller = this.$refs.pageScroller;
                let w = scroller.offsetWidth;
                let x = scroller.scrollLeft;
                let newX = w * Math.round(x / w);
                
                if (newX == 0) {
                    if (!this.checkPageScrollLeft) {
                        newX = w;
                    }
                } else
                if (newX == w * 2) {
                    if (!this.checkPageScrollRight) {
                        newX = w;
                    }
                }
                
                this.pageScrollAnimateTo = newX;
            }, 50);
        },
        onResizeWindow: function () {
            if (window.innerWidth > 668) return ;

            this.pageScrollAnimateTo = false;
            let scroller = this.$refs.pageScroller;
            if (scroller === undefined) return ;
            let w = scroller.offsetWidth;
            scroller.scrollLeft = w;
        },
        onPageScrollAnimate: function () {
            if (this.pageScrollAnimateTo === false) return ;
            if (this.pageScrollStart) return;
            if (window.innerWidth > 668) return;

            const step = 25;
            let scroller = this.$refs.pageScroller;
            if (scroller === undefined) return ;
            let x = scroller.scrollLeft;
            let w = scroller.offsetWidth;
            if (Math.abs(x - this.pageScrollAnimateTo) > step) {
                if (x > this.pageScrollAnimateTo) {
                    scroller.scrollLeft -= step;
                } else {
                    scroller.scrollLeft += step;
                }
            } else {
                if (this.pageScrollAnimateTo == 0) {
                    this.showPageLeft();
                } else 
                if (this.pageScrollAnimateTo == w * 2) {
                    this.showPageRight();
                }
                
                scroller.scrollLeft = this.pageScrollAnimateTo;
                this.pageScrollAnimateTo = false;
            }
        },
        showPageLeft: function () {
            let path = router.currentRoute.value.path;
            
            if (path.indexOf('/device/') > -1) {
                router.back();
                return ;
            }
            
            if (path == '/settings') {
                router.push({path: '/favorites'});
                return ;
            }
            
            router.push({path: '/'});
        },
        showPageRight: function () {
            router.push({path: '/favorites'});
        }
    },
})
.use(router);

app.config.globalProperties.emitter = emitter;
app.component('Spinner', Spinner);
app.component('Login', Login);
app.mount('#app');