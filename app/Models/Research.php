<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Research extends Model
{
    use HasFactory;

    protected $table = 'research';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $timestamps = true;
    public $incrementing = true;

    protected $fillable = [
        'leader_name',
        'leaders_nidn',
        'leaders_institution',
        'title',
        'scheme_short_name',
        'scheme_name',
        'approved_funds',
        'proposed_year',
        'implementation_year',
        'focus_area',
        'funded_institution_name',
        'grant_program',
    ];

    public function authors()
    {
        return $this->belongsToMany(Author::class);
    }
}
