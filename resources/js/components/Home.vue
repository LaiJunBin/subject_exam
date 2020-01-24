<template>
    <div class="container mt-2">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">

                <h1>題庫</h1>
                <div class="form-group">
                    <label for="category">選擇題庫</label>
                    <select class="custom-select" v-model="category" id="category">
                        <option v-for="(category,index) in categories" :value="category.id">
                            {{ category.name }}
                        </option>
                    </select>
                </div>
                <router-link :to="'categories/'+category+'/questions'" class="btn btn-success" >查看題目</router-link>

            </div>
        </div>

    </div>

</template>
​
<script>
    import api from '../api';

    export default {
        data(){
            return {
                category: ''
            }
        },
        computed:{
            categories(){
                let categories = this.$store.getters.getCategories;
                if(categories.length > 0)
                    this.category = categories[0].id;
                return categories;
            },
        },
        mounted:function(){
            this.$store.dispatch('getCategories');
        }
    }
</script>