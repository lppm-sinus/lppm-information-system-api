<?php

namespace App\Imports;

use App\Models\Author;
use App\Models\StudyProgram;
use Maatwebsite\Excel\Concerns\ToModel;

class AuthorImport implements ToModel
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

        if (Author::where('nidn', $row[2])->exists()) {
            throw new \Exception(json_encode([
                'success' => false,
                'message' => 'Author with NIDN ' . $row[2] . ' already exists.'
            ]));
        }

        $study_program_name = $row[5];
        if ($study_program_name) {
            $study_program = StudyProgram::firstOrCreate(
                ['name' => $study_program_name]
            );
        }

        return new Author([
            'sinta_id' => $row[1],  // Skip the "NO" column (index 0)
            'nidn' => $row[2],
            'name' => $row[3],
            'affiliation' => $row[4],
            'study_program_id' => $study_program_name ? $study_program->id : null,
            'last_education' => $row[6],
            'functional_position' => $row[7],
            'title_prefix' => $row[8],
            'title_suffix' => $row[9],
            'sinta_score' => $row[10],
        ]);
    }
}
