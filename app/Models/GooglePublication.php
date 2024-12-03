<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GooglePublication extends Model
{
    use HasFactory;

    protected $table = 'google_publications';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $timestamps = true;
    public $incrementing = true;

    protected $fillable = [
        'accreditation',
        'title',
        'journal',
        'creators',
        'year',
        'citation',
    ];

    public function authors()
    {
        return $this->belongsToMany(Author::class, 'author_google_publication', 'google_publication_id', 'author_id');
    }
}
