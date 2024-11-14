<?php

namespace App\Http\Controllers;

use App\Imports\ServiceImport;
use App\Models\Author;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class ServiceController extends Controller
{
    use ApiResponse;

    public function __construct()
    {
        $this->middleware(['role:superadmin|admin']);
    }

    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:csv,xls,xlsx',
        ]);

        if ($validator->fails()) {
            return $this->formatValidationErrors($validator);
        }

        try {
            $import = new ServiceImport();
            $collection = Excel::toCollection($import, $request->file('file'))->first();

            foreach ($collection as $row) {
                if ($row[0] == 'NO' || empty($row[1])) {
                    continue;
                }

                $author = Author::where('nidn', $row[2])->first();
                if (!$author) {
                    throw new \Exception('Author with nidn ' . $row[2] . ' not found.');
                }
            }

            if ($request->boolean('reset_table')) {
                DB::table('author_service')->delete();
                DB::table('services')->delete();
            }

            Excel::import($import, $request->file('file'));

            return $this->successResponse(null, 'Services data imported successfully.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }
}
