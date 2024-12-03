<?php

namespace App\Imports;

use App\Models\GooglePublication;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class GoogleImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $accreditation = $row['accreditation'] !== '-' ? $row['accreditation'] : 'Jurnal Nasional';

        return new GooglePublication([
            'accreditation' => $accreditation,
            'title' => $row['title'],
            'journal' => $row['journal'],
            'creators' => $row['authors'],
            'year' => $row['year'],
            'citation' => $row['citation'],
        ]);
    }

    public function headingRow(): int
    {
        return 5;
    }
}
