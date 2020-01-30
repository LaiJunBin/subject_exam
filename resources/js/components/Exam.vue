<template>
    <div class="container my-3">
        <div class="row" v-if="isExam===false">
            <div class="col-md-8 col-md-offset-2">

                <h1>設定測驗資訊</h1>

                <div class="form-group">
                    <label for="single-count">單選題數</label>
                    <input type="number" class="form-control" name="single-count" id="single-count" placeholder="請輸入單選題數" v-model.number="singleCount">
                </div>
                <div class="form-group">
                    <label for="multiple-count">複選題數</label>
                    <input type="number" class="form-control" name="multiple-count" id="multiple-count" placeholder="請輸入複選題數" v-model.number="multipleCount">
                </div>

                <div class="form-group">
                    <label for="category">選擇題庫</label>
                    <div class="question-options">
                        <span v-for="(category,index) in categories" class="question-option"
                                :class="{
                                    'option-checked': payload.categories.includes(category.id),
                                }">
                            <input :id="'category-'+category.id"
                                    type="checkbox"
                                    :value="category.id"
                                    v-model="payload.categories"
                            ><label :for="'category-'+category.id" class="form-check-label">{{ category.name }}</label>
                            <!--v-model="answers[question.id]"
                                    :checked="Array.isArray(answers[question.id]) && answers[question.id].includes(category.id)"
                            -->
                        </span>
                    </div>
                    <div class="form-group">
                      <label for="proportion-min">最小占比(%)</label>
                      <input type="number" class="form-control" name="proportion-min" id="proportion-min" placeholder="請輸入最小比率(Ex:90)" v-model.number="payload.proportion[0]">
                    </div>
                    <div class="form-group">
                      <label for="proportion-max">最大占比(%)</label>
                      <input type="number" class="form-control" name="proportion-max" id="proportion-max" placeholder="請輸入最大比率(Ex:95)" v-model.number="payload.proportion[1]">
                    </div>
                </div>
                <button class="btn"
                        :class="{
                            'btn-success': payload.editIndex===undefined,
                            'btn-warning': payload.editIndex!==undefined,
                        }"
                        @click="addPayload"
                        v-text="(payload.editIndex===undefined?'加入':'修改')+'測驗資訊'"></button>

                <div v-if="payloads.length > 0">
                    <hr>
                    <h1>確認測驗資訊</h1>
                    <table class="table table-striped table-inverse table-responsive d-table">
                        <thead class="thead-inverse">
                            <tr>
                                <th>#</th>
                                <th>題庫</th>
                                <th>最小占比(%)</th>
                                <th>最大占比(%)</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(payload, index) in payloads">
                                    <td scope="row">{{ index+1 }}</td>
                                    <td>{{ payload.categories.map(categoryId => categories.find(category => category.id === categoryId).name).join(', ') }}</td>
                                    <td>{{ payload.proportion[0] }}</td>
                                    <td>{{ payload.proportion[1] }}</td>
                                    <td>
                                        <button class="btn btn-warning" @click="editPayload(index)">修改</button>
                                        <button class="btn btn-danger" @click="deletePayload(index)">刪除</button>
                                    </td>
                                </tr>
                            </tbody>
                    </table>

                    <button class="btn btn-success" @click="startExam">開始測驗</button>
                </div>
            </div>
        </div>


        <div class="row flex-column" v-if="isExam===true">
            <h1 class="question-title">
                <button class="btn btn-secondary" @click="isExam=false">返回</button>
                綜合測驗
                <button class="btn btn-info" @click="resetQuestions">重新測驗</button>
                <button class="btn btn-success" @click="judgeAnswers" v-if="Object.keys(answerOptions).length===0">送出評分</button>
                <a class="btn btn-light" href="#">回到頂端</a>
                <h2 class="d-inline-block" v-if="Object.keys(answerOptions).length>0">
                    得分：
                    <span :class="{
                        'text-danger': score<60,
                        'text-success': score>=60
                    }">{{ score }}</span>
                </h2>
            </h1>
            <main>
                <div v-for="(question, index) in questions" class="questions">
                    {{(index+1)+'.'+question.content}}
                    <div class="question-options">
                        <span v-for="(option,index) in question.options" class="question-option"
                                :class="{
                                    'option-checked': Array.isArray(answers[question.id]) && answers[question.id].includes(option.id),
                                    'option-correct': judgeResult[option.id] === true,
                                    'option-answer': answerOptions[question.id] && answerOptions[question.id].includes(option.id),
                                    'option-fail': judgeResult[option.id] === false,
                                }">
                            <input :id="'option-'+option.id"
                                   :type="question.answers.length===1?'radio':'checkbox'"
                                   :name="'question'+question.id"
                                   :value="option.id"
                                   :data-question-id="question.id"
                                   @change="onChange"
                                   :disabled="judgeResult.length>0"
                                   :checked="Array.isArray(answers[question.id]) && answers[question.id].includes(option.id)"
                            ><label :for="'option-'+option.id" class="form-check-label">{{ option.value }}</label>
                                <!--v-model="answers[question.id]"-->
                        </span>
                    </div>
                </div>
            </main>
        </div>


    </div>

</template>
​
<script>
    import api from '../api';
    import swal from "sweetalert";

    export default {
        data(){
            return {
                singleCount:0,
                multipleCount:0,
                payloads: [],
                payload:{
                    proportion:[0, 0],
                    categories:[]
                },
                isExam:false,
                answers: [],
                judgeResult: [],
                answerOptions: {},
                score: 0,
            }
        },
        computed:{
            categories(){
                let categories = this.$store.getters.getCategories;
                return categories;
            },
            questions(){
                let questions = this.$store.getters.getExamQuestions;
                this.answers = [];
                this.judgeResult = [];
                this.answerOptions = {};
                for(let question of questions){
                    this.answers[question.id] = [];
                }
                return questions;
            },
        },
        mounted:function(){
            this.$store.dispatch('getCategories');
        },
        methods: {
            addPayload:function(){
                if(this.payload.categories.length===0){
                    return swal('警告','至少必須選擇一個題庫!','warning');
                }

                if(this.payload.proportion[1] < this.payload.proportion[0]){
                    return swal('警告', '最大占比必須大於最小占比!', 'warning');
                }

                if(this.payload.proportion[0] < 0 || this.payload.proportion[1] < 0){
                    return swal('警告', '占比不得小於0!', 'warning');
                }

                if(this.payload.proportion[1] === 0){
                    return swal('警告', '最大占比不得等於0!', 'warning');
                }

                if(this.payload.editIndex===undefined){
                    this.payloads.push(this.payload);
                }else{
                    this.payloads[this.payload.editIndex] = this.payload;
                }

                this.payload = {
                    proportion:[0, 0],
                    categories:[]
                };

            },

            editPayload:function(index){
                this.payload = JSON.parse(JSON.stringify(this.payloads[index]));
                this.payload.editIndex = index;
            },

            deletePayload:function(index){
                this.payloads.splice(index, 1);
            },

            startExam:function(){
                if((this.singleCount + this.multipleCount) > 100){
                    return swal('警告', '總題數不得大於100題!', 'warning');
                }

                if((this.singleCount + this.multipleCount) <= 0){
                    return swal('警告', '總題數至少必須1題!', 'warning');
                }

                let totalSingleCount = this.payloads
                                        .map(payload =>
                                            payload.categories
                                            .map(categoryId =>
                                                this.categories
                                                .find(category =>
                                                    category.id === categoryId
                                                    ).info.single
                                                ).reduce((x, y) => x + y)
                                        ).reduce((x, y) => x + y);

                if(this.singleCount > totalSingleCount){
                    return swal('警告', '題庫單選題數量不足，請降低單選題數!', 'warning');
                }

                let totalMultipleCount = this.payloads
                                        .map(payload =>
                                            payload.categories
                                            .map(categoryId =>
                                                this.categories
                                                .find(category =>
                                                    category.id === categoryId
                                                    ).info.multiple
                                                ).reduce((x, y) => x + y)
                                        ).reduce((x, y) => x + y);

                if(this.multipleCount > totalMultipleCount){
                    return swal('警告', '題庫複選題數量不足，請降低複選題數!', 'warning');
                }

                this.$store.dispatch('getExam', {
                    single: this.singleCount,
                    multiple:this.multipleCount,
                    data:this.payloads
                }).then(res => {
                    if(res){
                        document.scrollingElement.scrollTop = 0;
                        this.isExam = true;
                    }
                });

            },

            onChange:function(e){
                if(this.judgeResult.length > 0){
                    return;
                }
                let questionId = e.target.dataset.questionId;
                let optionId = +e.target.value;
                let type = e.target.type;
                let checked = e.target.checked;

                if(type === 'radio'){
                    this.answers[questionId].splice(0, 1);
                    this.answers[questionId].push(optionId);
                }else{
                    if(checked){
                        this.answers[questionId].push(optionId);
                    }else{
                        this.answers[questionId].splice(this.answers[questionId].indexOf(optionId), 1);
                    }
                }

                this.$forceUpdate();
            },

            judgeAnswers:function(){
                this.judgeResult = [-1];
                this.answerOptions = Object.fromEntries(this.questions.map(question=>[question.id, question.answers.map(answer => answer.option_id)]));

                let status = {
                    correct: 0,
                    fail: 0,
                    unanswered: 0,
                };

                for(let questionId in this.answerOptions){
                    if(this.answers[questionId].length === 0){
                        status.unanswered++;
                        continue;
                    }

                    let bool = true;
                    for(let option of [...this.answerOptions[questionId], ...this.answers[questionId]]){
                        if(this.answers[questionId].includes(option) && this.answerOptions[questionId].includes(option)){
                            this.judgeResult[option] = true;
                        }else{
                            bool = false;
                            this.judgeResult[option] = false;
                        }
                    }

                    if(bool)
                        status.correct++;
                    else
                        status.fail++;
                }

                this.score = ((100 / (status.correct + status.fail + status.unanswered)) * status.correct).toFixed(2);
                swal('結果', `正確題數: ${status.correct}
                    錯誤題數: ${status.fail}
                    未作答題數: ${status.unanswered}`, 'info');
            },

            resetQuestions: function(){
                swal({
                    title: '提示',
                    text: '確定要重新測驗嗎?',
                    buttons: true,
                }).then(res => {
                    if(res){
                        this.$store.dispatch('getExam', {
                            single: this.singleCount,
                            multiple:this.multipleCount,
                            data:this.payloads
                        });
                    }
                });
            }
        },

    }
</script>