import Vue from 'vue';
import Vuex from 'vuex';
import api from './api';

Vue.use(Vuex);

export default new Vuex.Store({ // 可以設置多個模塊
    state: {
        categories: [],
        category: null,
        examQuestions: [],
        loading: true,
    },
    getters: {
        getCategories: state => state.categories,
        getCategory: state => state.category,
        getLoadingStatus: state => state.loading,
        getExamQuestions: state => state.examQuestions,
    },
    mutations: {
        setCategories: (state, categories) => state.categories = categories,
        setCategory: (state, category) => state.category = category,
        setLoadingStatus: (state, loading) => state.loading = loading,
        setExamQuestions: (state, examQuestions) => state.examQuestions = examQuestions,
    },
    actions: {
        getCategories({
            commit
        }) {
            if (this.state.categories.length === 0) {
                commit('setLoadingStatus', true);
                api.getCategories().then(res => {
                    commit('setCategories', res);
                    commit('setLoadingStatus', false);
                });
            }
        },
        getCategory(context, payload) {
            let id = payload.id;
            let category = null;
            context.commit('setLoadingStatus', true);
            api.getCategory(id).then(res => {
                category = res;
                api.getCategoryQuestions(id).then(res => {
                    category.questions = res;
                    context.commit('setCategory', category);
                    context.commit('setLoadingStatus', false);
                });
            });
        },

        getExam(context, payload) {
            context.commit('setLoadingStatus', true);
            return api.getExam(payload).then(res => {
                context.commit('setExamQuestions', res);
                context.commit('setLoadingStatus', false);
                return res;
            });
        }
    }
});
