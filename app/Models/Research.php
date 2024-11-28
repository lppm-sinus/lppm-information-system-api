<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Research extends Model
{
    use HasFactory;

    protected $table = 'researches';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $timestamps = true;
    public $incrementing = true;

    protected $fillable = [
        'nama_ketua',
        'nidn_ketua',
        'afiliasi_ketua',
        'kd_pt_ketua',
        'judul',
        'nama_singkat_skema',
        'thn_pertama_usulan',
        'thn_usulan_kegiatan',
        'thn_pelaksanaan_kegiatan',
        'lama_kegiatan',
        'bidang_fokus',
        'nama_skema',
        'status_usulan',
        'dana_disetujui',
        'afiliasi_sinta_id',
        'nama_institusi_penerima_dana',
        'target_tkt',
        'nama_program_hibah',
        'kategori_sumber_dana',
        'negara_sumber_dana',
        'sumber_dana',
    ];

    public function authors()
    {
        return $this->belongsToMany(Author::class, 'author_research', 'research_id', 'author_id');
    }
}
