import VueRouter from 'vue-router';

let routes = [{
        path: '/',
        component: require('./components/Home').default
    },
    {
        path: '/exam',
        component: require('./components/Exam').default
    },
    {
        path: '/categories/:category_id/questions',
        component: require('./components/ViewQuestions').default
    },
    {
        path: '*',
        redirect: '/'
    }
];

export default new VueRouter({
    mode: 'history',
    base: '/subject_exam',
    history: true,
    routes
});
