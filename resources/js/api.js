import axios from 'axios';
import swal from "sweetalert";

export default {
    getCategories: function () {
        return axios.get('/api/categories').then(res => {
            if (res.data.status)
                return res.data.data;

            console.error(res.data);
            swal('錯誤!', Object.entries(res.data.data).map(d => d[1]).join('\n'), 'warning');
            return false;
        })
    },
    getCategory: function (id) {
        return axios.get(`/api/categories/${id}`).then(res => {
            if (res.data.status)
                return res.data.data;

            console.error(res.data);
            swal('錯誤!', Object.entries(res.data.data).map(d => d[1]).join('\n'), 'warning');
            return false;
        });
    },
    getCategoryQuestions: function (id) {
        return axios.get(`/api/categories/${id}/questions`).then(res => {
            if (res.data.status)
                return res.data.data;

            console.error(res.data);
            swal('錯誤!', Object.entries(res.data.data).map(d => d[1]).join('\n'), 'warning');
            return false;
        });
    },
    getExam: function (payload) {
        return axios.post('/api/exam', payload).then(res => {
            if (res.data.status)
                return res.data.data;

            console.error(res);
            swal('錯誤!', Object.entries(res.data.data).map(d => d[1]).join('\n'), 'warning');
            return false;
        })
    }
};
