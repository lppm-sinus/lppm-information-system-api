<?php

namespace App\Imports;

use App\Models\Author;
use App\Models\Service;
use Maatwebsite\Excel\Concerns\ToModel;

class ServiceImport implements ToModel
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Ignore rows that contain headers like "NO" or if the row is empty
        if ($row[0] == 'NO' || empty($row[1])) {
            return null;
        }

        $author = Author::where('nidn', $row[2])->first();

        $numeric_string = str_replace(['Rp.', '.'], '', $row[7]);
        $decimal_value = (float) $numeric_string;

        $service = Service::create([
            'leader_name' => $row[1],
            'leaders_nidn' => $row[2],
            'leaders_institution' => $row[3],
            'title' => $row[4],
            'scheme_short_name' => $row[5],
            'scheme_name' => $row[6],
            'approved_funds' => $decimal_value,
            'proposed_year' => $row[8],
            'implementation_year' => $row[9],
            'focus_area' => $row[10],
            'funded_institution_name' => $row[11],
            'grant_program' => $row[12],
        ]);

        // Attach the author to the service
        $service->authors()->attach($author->id);

        return $service;
    }
}
