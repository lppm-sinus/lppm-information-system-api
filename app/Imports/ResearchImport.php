<?php

namespace App\Imports;

use App\Models\Author;
use App\Models\Research;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ResearchImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        try {
            $service = Research::create([
                'nama_ketua' => $row['nama_ketua'],
                'nidn_ketua' => $row['nidn_ketua'],
                'afiliasi_ketua' => $row['afiliasi_ketua'],
                'kd_pt_ketua' => $row['kd_pt_ketua'],
                'judul' => $row['judul'],
                'nama_singkat_skema' => $row['nama_singkat_skema'],
                'thn_pertama_usulan' => $row['thn_pertama_usulan'],
                'thn_usulan_kegiatan' => $row['thn_usulan_kegiatan'],
                'thn_pelaksanaan_kegiatan' => $row['thn_pelaksanaan_kegiatan'],
                'lama_kegiatan' => $row['lama_kegiatantahun'],
                'bidang_fokus' => $row['bidang_fokus'],
                'nama_skema' => $row['nama_skema'],
                'status_usulan' => $row['status_usulan_hanya_didanai'],
                'dana_disetujui' => (float) $row['dana_disetujui'],
                'afiliasi_sinta_id' => $row['afiliasi_sinta_id'],
                'nama_institusi_penerima_dana' => $row['nama_institusi_penerima_dana'],
                'target_tkt' => $row['target_tkt'],
                'nama_program_hibah' => $row['nama_program_hibah'],
                'kategori_sumber_dana' => $row['kategori_sumber_dana'],
                'negara_sumber_dana' => $row['negara_sumber_dana'],
                'sumber_dana' => $row['sumber_dana'],
            ]);

            $nidns = array_filter([
                'nidn_ketua' => $row['nidn_ketua'] ?? null,
                'nidn_member1' => $row['nidn_member1'] ?? null,
                'nidn_member_sinta2' => $row['nidn_member_sinta2'] ?? null,
                'nidn_member_sinta3' => $row['nidn_member_sinta3'] ?? null,
                'nidn_member_sinta4' => $row['nidn_member_sinta4'] ?? null,
                'nidn_member_sinta5' => $row['nidn_member_sinta5'] ?? null,
            ]);

            // Attach the authors to the services
            foreach ($nidns as $field => $nidn) {
                if ($nidn) {
                    try {
                        $author_id = $this->getAuthorID($nidn);
                        $service->authors()->attach($author_id);
                    } catch (\Exception $e) {
                        DB::rollBack();
                        throw new \Exception($field . ": " . $e->getMessage());
                    }
                }
            }

            DB::commit();
            return $service;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function getAuthorID($nidn)
    {
        $author = Author::where('nidn', $nidn)->first();

        if (!$author) {
            throw new \Exception("Author with NIDN {$nidn} not found.");
        }

        return $author->id;
    }

    public function rules(): array
    {
        return [
            'nama_ketua' => 'required',
            'nidn_ketua' => 'required',
        ];
    }
}
