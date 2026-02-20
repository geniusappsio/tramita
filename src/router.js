import Vue from 'vue'
import Router from 'vue-router'

Vue.use(Router)

export default new Router({
	routes: [
		{
			path: '/',
			name: 'dashboard',
			component: () => import('./views/Dashboard.vue'),
		},
		{
			path: '/board/:processTypeId',
			name: 'kanbanBoard',
			component: () => import('./views/KanbanBoard.vue'),
		},
		{
			path: '/request/new/:processTypeId',
			name: 'newRequest',
			component: () => import('./views/RequestForm.vue'),
		},
		{
			path: '/request/:id',
			name: 'requestDetail',
			component: () => import('./views/RequestDetail.vue'),
		},
		{
			path: '/request/:id/edit',
			name: 'editRequest',
			component: () => import('./views/RequestForm.vue'),
		},
	],
})
