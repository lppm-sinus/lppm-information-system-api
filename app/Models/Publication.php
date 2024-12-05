<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Publication extends Model
{
    use HasFactory;

    protected $table = 'publications';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $timestamps = true;
    public $incrementing = true;

    protected $fillable = [
        'accreditation',
        'identifier',
        'quartile',
        'title',
        'journal',
        'publication_name',
        'creators',
        'year',
        'citation',
        'category',
    ];

    public function authors()
    {
        return $this->belongsToMany(Author::class, 'author_publication', 'publication_id', 'author_id');
    }
}
