<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use \Validator;

use App\Category;
use App\Question;
use App\Traits\ApiTrait;

class ApiController extends Controller
{

    use ApiTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = Category::get()->map(function($category){
            $questions = $category->questions;
            $category->info = [
                'single' => $questions->filter(function($question){
                    return $question->answers->count() === 1;
                })->count(),
                'multiple' => $questions->filter(function($question){
                    return $question->answers->count() > 1;
                })->count(),
            ];
            return $category->only([
                'id',
                'name',
                'info'
            ]);
        });
        return $this->apiResponse(true, $categories);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|unique:categories|max:100'
        ];

        $validator = Validator::make($request->all(), $rules);

        if($validator->fails())
            return $this->apiResponse(false, $validator->errors());

        $category = Category::create([
            'name' => $request->name
        ]);

        return $this->apiResponse(true, $category->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            return $this->apiResponse(true, Category::findOrFail($id));
        } catch (\Throwable $th) {
            return $this->apiResponse(false, 'Category Not Found.');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        dd($request->post());
        try {
            $category = Category::findOrFail($id);
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }


    public function getQuestions($id){
        try {
            $category = Category::findOrFail($id);
            $questions = $category->questions->map(function($question){
                $question->options = $question->options->map(function($option){
                    $option->value = trim($option->value);
                    return $option->only([
                        'id',
                        'value'
                    ]);
                })->shuffle();
                $question->answers = $question->answers->map(function($answer){
                    return $answer->option_id;
                });
                return $question->only([
                    'id',
                    'answers',
                    'content',
                    'options'
                ]);
            });

            return $this->apiResponse(true, $questions);
        } catch (\Throwable $th) {
            return $this->apiResponse(false, 'Category Not Found.');
        }
    }

    public function getExam(Request $request){
        $rules = [
            'single' => 'required|numeric|min:0',
            'multiple' => 'required|numeric|min:0',
            'data' => [
                'required',
                function($attr, $value, $fail){
                    $min = array_sum(array_map(function($d){
                        return min($d['proportion']);
                    }, $value));

                    if($min > 100)
                        return $fail('最小占比的總和必須小於100');

                    $max = array_sum(array_map(function($d){
                        return max($d['proportion']);
                    }, $value));

                    if($max < 100)
                        return $fail('最大占比的總和必須大於100');

                }
            ],
            'data.*.proportion' => [
                'array',
                'between:1,2',
                function($attr, $values, $fail){
                    if(count($values) === 2 && $values[0] > $values[1])
                        return $fail('最大占比必須大於最小占比');
                }
            ],
            'data.*.proportion.*' => 'integer',
            'data.*.categories.*' => 'exists:categories,id'
        ];

        $validator = Validator::make($request->all(), $rules);

        if($validator->fails())
            return $this->apiResponse(false, $validator->messages());

        $data = collect($request->data)->map(function($d) use($request){
            $questions = Question::whereIn('category_id', $d['categories'])->get();
            $d['single_questions'] = $questions->filter(function($question){
                                        return $question->answers->count() === 1;
                                    })->shuffle();
            $d['multiple_questions'] = $questions->filter(function($question){
                return $question->answers->count() > 1;
            })->shuffle();
            $d['single_min'] = floor($d['proportion'][0] * $request->single / 100);
            $d['single_max'] = floor($d['proportion'][1] * $request->single / 100);
            $d['multiple_min'] = floor($d['proportion'][0] * $request->multiple / 100);
            $d['multiple_max'] = floor($d['proportion'][1] * $request->multiple / 100);
            return $d;
        });

        $questions = collect();
        $single_count = 0;
        $multiple_count = 0;
        for($i = 0; $i < count($data); $i++){
            for($j = 1; $j <= min($data[$i]['single_min'], count($data[$i]['single_questions'])); $j++){
                $questions->push($data[$i]['single_questions']->pop());
                $single_count++;
            }
            for($j = 1; $j <= min($data[$i]['multiple_min'], count($data[$i]['multiple_questions'])); $j++){
                $questions->push($data[$i]['multiple_questions']->pop());
                $multiple_count++;
            }
        }

        $questions_temp = $data->map(function($d){
            $temp['single_questions'] = $d['single_questions']->splice(0, $d['single_max'] - $d['single_min']);
            $temp['multiple_questions'] = $d['multiple_questions']->splice(0, $d['multiple_max'] - $d['multiple_min']);
            return $temp;
        });
        $single_questions_temp = $questions_temp->pluck('single_questions')->collapse();
        $single_questions_temp_add_count = min($request->single-$single_count, $single_questions_temp->count());
        $multiple_questions_temp = $questions_temp->pluck('multiple_questions')->collapse();
        $multiple_questions_temp_add_count = min($request->multiple-$multiple_count, $multiple_questions_temp->count());
        $questions = $questions->merge($single_questions_temp
                                ->splice(0, $single_questions_temp_add_count));
        $questions = $questions->merge($multiple_questions_temp
                                ->splice(0, $multiple_questions_temp_add_count));
        $single_count += $single_questions_temp_add_count;
        $multiple_count += $multiple_questions_temp_add_count;

        $total_single = $data->pluck(['single_questions'])->collapse()->merge($single_questions_temp);
        $total_multiple = $data->pluck(['multiple_questions'])->collapse()->merge($multiple_questions_temp);
        $questions = $questions->merge($total_single
                                ->splice(0, min($request->single-$single_count, $total_single->count())));
        $questions = $questions->merge($total_multiple
                                ->splice(0, min($request->multiple-$multiple_count, $total_multiple->count())));


        $questions = $questions->map(function($question){
            $question->answers = $question->answers->map(function($answer){
                return $answer->only(['option_id']);
            })->toarray();
            $question->options = $question->options->map(function($option){
                return $option->only(['id', 'value']);
            })->toarray();
            return $question->only([
                'id',
                'content',
                'options',
                'answers'
            ]);
        }, $questions);

        return $this->apiResponse(true, $questions);
    }
}
