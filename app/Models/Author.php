<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    use HasFactory;
    protected $fillable = [
        'sinta_id',
        'nidn',
        'name',
        'affiliation',
        'study_program_id',
        'last_education',
        'functional_position',
        'title_prefix',
        'title_suffix',
    ];

    public function studyProgram()
    {
        return $this->belongsTo(StudyProgram::class);
    }
}
