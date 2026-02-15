import Vue from 'vue'
import AdminSettings from './views/AdminSettings.vue'
import { createPinia, PiniaVuePlugin } from 'pinia'

Vue.use(PiniaVuePlugin)

const pinia = createPinia()

export default new Vue({
	el: '#tramita-admin-settings',
	pinia,
	render: h => h(AdminSettings),
})
