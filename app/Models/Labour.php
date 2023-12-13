<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Labour extends Model
{

    protected $table = 'labour_details';
    protected $fillable = ['id','name','job_title','phone','gender','salary','government_image','advance_amt','salary_type'];
}
?>
