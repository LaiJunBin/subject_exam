<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $guarded = [];

    public function category(){
        return $this->belongsTo(Category::class);
    }

    public function options(){
        return $this->hasMany(Option::class);
    }

    public function answers(){
        return $this->hasMany(Answer::class);
    }
}
