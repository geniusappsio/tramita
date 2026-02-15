import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'

const baseUrl = generateUrl('/apps/tramita')

export const api = axios.create({
	baseURL: baseUrl,
})

// Interceptor to handle 402 (license required)
api.interceptors.response.use(
	response => response,
	error => {
		if (error.response?.status === 402) {
			window.dispatchEvent(new CustomEvent('tramita:license-required'))
		}
		return Promise.reject(error)
	},
)
