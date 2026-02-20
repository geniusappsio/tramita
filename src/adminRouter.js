import Vue from 'vue'
import Router from 'vue-router'

Vue.use(Router)

export default new Router({
	mode: 'hash',
	routes: [
		{
			path: '/',
			name: 'processTypes',
			component: () => import('./views/ProcessTypeList.vue'),
		},
		{
			path: '/:id/stages',
			name: 'stageManager',
			component: () => import('./views/StageManager.vue'),
		},
		{
			path: '/:id/form',
			name: 'formEditor',
			component: () => import('./views/FormTemplateEditor.vue'),
		},
	],
})
