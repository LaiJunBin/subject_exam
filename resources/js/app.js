/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */
import VueRouter from 'vue-router';
import router from './routes';
import store from './store';

require('./bootstrap');
require('./prototype');
window.Vue = require('vue');
Vue.use(VueRouter);
/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

// Vue.component('example-component', require('./components/ExampleComponent.vue').default);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */
Vue.component('pulse-loader', require('vue-spinner/src/PulseLoader.vue').default);
const app = new Vue({
    el: '#app',
    router,
    store,
    computed: {
        loading() {
            return this.$store.getters.getLoadingStatus;
        }
    },
    mounted: function () {
        var scrollY = 0;

        document.addEventListener('scroll', function (e) {
            let windowScrollY = window.scrollY;
            if (Math.abs(windowScrollY - scrollY) >= 200) {
                if (Math.sign(windowScrollY - scrollY) === 1) {
                    $(".question-title").slideUp();
                } else {
                    $(".question-title").slideDown();
                }
                scrollY = windowScrollY;
            }
        })
    }
});
