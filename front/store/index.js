import Cookies from 'js-cookie'
import {cookieFromRequest} from '~/utils'

export const mutations = {
    SET_BASE(state, base) {
        state.base = base
    }
}

export const actions = {
    nuxtServerInit({commit, getters}, {req, app}) {
        const token = cookieFromRequest(req, 'token')
        if (token) {
            commit('auth/SET_TOKEN', token)
        }

        let locale = cookieFromRequest(req, 'prefer_lang')
        if (locale) {
            commit('lang/SET_LOCALE', {locale})
        }

        // const base = getters["lang/checkLocales"] > 1 ? getters["lang/locale"] : '/'
        // commit('SET_BASE', base)
        // app.router.options.base = base
        // app.router.history.base = `/${base}`
    },

    nuxtClientInit({commit, getters}, {app}) {
        const token = Cookies.get('token')
        if (token) {
            commit('auth/SET_TOKEN', token)
        }

        const locale = Cookies.get('prefer_lang')
        if (locale) {
            commit('lang/SET_LOCALE', {locale})
        }

        // const base = getters["lang/checkLocales"] > 1 ? getters["lang/locale"] : '/'
        // app.router.options.base = base
        // app.router.history.base = `/${base}`
    }
}
