<?php

use Illuminate\Database\Seeder;

use App\Category;
use App\Option;
use App\Answer;
use App\Question;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);

        $dataset = [
            '網頁設計 乙級' => '173002A12.txt',
            "職業安全衛生共同科目" => '900060A14.txt',
            "工作倫理與職業道德共同科目" => '900070A14.txt',
            "環境保護共同科目" => '900080A14.txt',
            "節能減碳共同科目" => '900090A14.txt',
            "資訊相關職類共用工作項目" => '900110A10.txt',
        ];

        foreach($dataset as $category => $filename){
            $category = Category::create(['name' => $category]);
            $raw_data = file_get_contents(__DIR__."/../data/{$filename}");
            $questions = array_map(function($d){
                return $d.'。';
            }, array_filter(explode('。', $raw_data), function($d){
                return trim($d) !== '';
            }));

            foreach($questions as $question){
                preg_match('/\(([0-9]+)\)([^①]+).([^②]+).([^③]+).([^④]+).([^。]+)/u', $question, $match);
                $question = Question::create([
                    'category_id' => $category->id,
                    'content' => $match[2]
                ]);
                $options = [
                    Option::create(['question_id' => $question->id, 'value' => $match[3]]),
                    Option::create(['question_id' => $question->id, 'value' => $match[4]]),
                    Option::create(['question_id' => $question->id, 'value' => $match[5]]),
                    Option::create(['question_id' => $question->id, 'value' => $match[6]]),
                ];

                foreach(str_split($match[1]) as $option_id){
                    Answer::create([
                        'question_id' => $question->id,
                        'option_id' => $options[$option_id-1]->id
                    ]);
                }
            }
        }



    }
}
