<template>
    <div class="container mt-2">
        <div class="row flex-column" v-if="category">
            <h1>
                {{ category.name }}
                <button class="btn" @click="viewAnswer=!viewAnswer" :class="viewAnswer?'btn-dark':'btn-info'" v-text="(viewAnswer?'隱藏':'檢視')+'答案'"></button>
                <button class="btn btn-warning" @click="shuffle">打亂順序</button>
                <button class="btn btn-secondary" @click="resetAnswers">重置答案</button>
                <button class="btn btn-success" @click="judgeAnswers">檢查結果</button>
            </h1>
            <main>
                <div v-for="(question, index) in category.questions" class="questions">
                    {{(index+1)+'.'+question.content}}
                    <div class="question-options">
                        <span v-for="(option,index) in question.options" class="question-option"
                                :class="{
                                    // 'option-checked':answers[question.id] === option.id ||
                                    //     (Array.isArray(answers[question.id]) && answers[question.id].includes(option.id)),
                                    'option-checked': Array.isArray(answers[question.id]) && answers[question.id].includes(option.id),
                                    'option-answer': viewAnswer && question.answers.includes(option.id),
                                    'option-correct': judgeResult[option.id] === true,
                                    'option-fail': judgeResult[option.id] === false,
                                }">
                            <input :id="'option-'+option.id"
                                   :type="question.answers.length===1?'radio':'checkbox'"
                                   :name="'question'+question.id"
                                   :value="option.id"
                                   :data-question-id="question.id"
                                   @change="onChange"
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
                viewAnswer:false,
                answers: [],
                judgeResult: []
            }
        },
        computed:{
            category(){
                let category = this.$store.getters.getCategory;
                if(category!==null){
                    this.answers = [];
                    for(let question of category.questions){
                        this.answers[question.id] = [];
                    }
                }

                return category;
            }
        },
        mounted:function(){
            let category_id = this.$route.params.category_id;
            this.$store.dispatch('getCategory', {id:category_id});
        },
        methods:{
            onChange:function(e){
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

            shuffle:function(){
                for (let i = this.category.questions.length - 1; i > 0; i--) {
                    const j = Math.floor(Math.random() * (i + 1));
                    [this.category.questions[i], this.category.questions[j]] = [this.category.questions[j], this.category.questions[i]];
                }
                this.$forceUpdate();
            },

            resetAnswers:function(){
                this.judgeResult = [];
                for(let question of this.category.questions){
                    this.answers[question.id] = [];
                }
            },

            judgeAnswers:function(){
                this.judgeResult = [];
                let answerOptions = Object.fromEntries(this.category.questions.map(question=>[question.id, question.answers]));

                let status = {
                    correct: 0,
                    fail: 0,
                    unanswered: 0,
                };

                for(let questionId in answerOptions){
                    if(this.answers[questionId].length === 0){
                        status.unanswered++;
                        continue;
                    }

                    let bool = true;
                    for(let option of [...answerOptions[questionId], ...this.answers[questionId]]){
                        if(this.answers[questionId].includes(option) && answerOptions[questionId].includes(option)){
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

                swal('結果', `正確題數: ${status.correct}
                    錯誤題數: ${status.fail}
                    未作答題數: ${status.unanswered}`, 'info');
            }
        }

    }
</script>