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
			path: '/process-types',
			name: 'processTypes',
			component: () => import('./views/ProcessTypeList.vue'),
		},
		{
			path: '/process-types/:id/stages',
			name: 'stageManager',
			component: () => import('./views/StageManager.vue'),
		},
		{
			path: '/process-types/:id/form',
			name: 'formEditor',
			component: () => import('./views/FormTemplateEditor.vue'),
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
		{
			path: '/admin/settings',
			name: 'adminSettings',
			component: () => import('./views/AdminSettings.vue'),
		},
	],
})
