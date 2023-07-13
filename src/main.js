/**
 * SPDX-FileCopyrightText: 2018 John Molakvoæ <skjnldsv@protonmail.com>
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import { generateFilePath } from '@nextcloud/router'

import Vue from 'vue'
import App from './App.vue'

import VueRouter from 'vue-router'
import { router } from './router/index.js'

import './styles/reset.css'
import './styles/base.css'

// eslint-disable-next-line
__webpack_public_path__ = generateFilePath(appName, '', 'js/')

Vue.mixin({ methods: { t, n } })
Vue.use(VueRouter)

export default new Vue({
	el: '#content',
	router,
	render: h => h(App),
})
