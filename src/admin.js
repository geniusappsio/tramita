import Vue from 'vue'
import adminRouter from './adminRouter.js'
import AdminSettings from './views/AdminSettings.vue'
import { createPinia, PiniaVuePlugin } from 'pinia'

Vue.use(PiniaVuePlugin)

const pinia = createPinia()

document.addEventListener('DOMContentLoaded', () => {
	const el = document.getElementById('tramita-admin-settings')
	if (el) {
		new Vue({
			el,
			pinia,
			router: adminRouter,
			render: h => h(AdminSettings),
		})
	}
})
