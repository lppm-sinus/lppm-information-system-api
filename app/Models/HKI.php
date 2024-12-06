<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HKI extends Model
{
    use HasFactory;

    protected $table = 'hkis';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $timestamps = true;
    public $incrementing = true;

    protected $fillable = [
        'tahun_permohonan',
        'nomor_permohonan',
        'kategori',
        'title',
        'pemegang_paten',
        'inventor',
        'status',
        'nomor_publikasi',
        'tanggal_publikasi',
        'filing_date',
        'reception_date',
        'nomor_registrasi',
        'tanggal_registrasi',
    ];

    public function authors()
    {
        return $this->belongsToMany(Author::class, 'author_hki', 'hki_id', 'author_id');
    }
}
