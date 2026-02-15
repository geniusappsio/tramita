import Vue from 'vue'
import App from './App.vue'
import router from './router.js'
import { createPinia, PiniaVuePlugin } from 'pinia'

Vue.use(PiniaVuePlugin)

const pinia = createPinia()

export default new Vue({
	el: '#content',
	router,
	pinia,
	render: h => h(App),
})
