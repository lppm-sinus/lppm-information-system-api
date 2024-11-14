<?php

namespace App\Http\Controllers;

use App\Imports\ResearchImport;
use App\Models\Author;
use App\Models\Research;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class ResearchController extends Controller
{
    use ApiResponse;


    public function __construct()
    {
        $this->middleware(['role:superadmin|admin']);
    }

    /**
     * @OA\Post(
     *     path="/api/researches/import",
     *     tags={"Research"},
     *     summary="Import research from excel file",
     *     security={{"bearer_token":{}}},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data", 
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="file",
     *                     type="string",
     *                     format="binary"
     *                 ),
     *                 @OA\Property(
     *                     property="reset_table",
     *                     type="boolean",
     *                 ),
     *                 example={"file": "authors.xlsx"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Research Data Imported",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Research data imported successfully."),
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Research with NIDN 345525656 already exists."),
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated access",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     * )
     */
    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        if ($validator->fails()) {
            return $this->formatValidationErrors($validator);
        }

        try {
            $import = new ResearchImport();
            $collection = Excel::toCollection($import, $request->file('file'))->first();

            foreach ($collection as $row) {
                if ($row[0] == 'NO' || empty($row[1])) {
                    continue;
                }

                $author = Author::where('nidn', $row[2])->first();
                if (!$author) {
                    throw new \Exception('Author with NIDN ' . $row[2] . ' not found.');
                }
            }

            if ($request->boolean('reset_table')) {
                DB::table('author_research')->delete();
                DB::table('research')->delete();
            }

            Excel::import($import, $request->file('file'));

            return $this->successResponse(null, 'Research data imported successfully.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/researches",
     *     tags={"Research"},
     *     summary="Create new research",
     *     security={{"bearer_token":{}}},
     *  @OA\RequestBody(
     *     required=true,
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *             @OA\Property(
     *                 property="leader_name",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="leaders_nidn",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="leaders_institution",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="title",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="scheme_short_name",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="scheme_name",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="approved_funds",
     *                 type="integer"
     *             ),
     *             @OA\Property(
     *                 property="proposed_year",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="implementation_year",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="focus_area",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="funded_institution_name",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="grant_program",
     *                 type="string"
     *             ),
     *             example={"leader_name": "Yustina", "leaders_nidn": "2342453", "leaders_institution": "Sekolah Tinggi Manajemen Informatika dan Komputer Sinar Nusantara", "title": "Penelitian 1", "scheme_short_name": "PI", "scheme_name": "Penelitian Internal", "approved_funds": 2000000, "proposed_year": "2024", "implementation_year": "2024", "focus_area": "Penelitian", "funded_institution_name": "Sekolah Tinggi Manajemen Informatika dan Komputer Sinar Nusantara", "grant_program": "Program Pengembangan"}
     *         )
     *     )
     * ),
     *     @OA\Response(
     *         response=201,
     *         description="Research Created",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Research created successfully."),
     *             @OA\Property(property="data", type="object",
     *                  @OA\Property(property="id", type="integer", example=1),
     *                  @OA\Property(property="leader_name", type="string", example="Yustina"),
     *                  @OA\Property(property="leaders_nidn", type="string", example="2342453"),
     *                  @OA\Property(property="leaders_institution", type="string", example="Sekolah Tinggi Manajemen Informatika dan Komputer Sinar Nusantara"),
     *                  @OA\Property(property="title", type="string", example="Penelitian 1"),
     *                  @OA\Property(property="scheme_short_name", type="string", example="PI"),
     *                  @OA\Property(property="scheme_name", type="string", example="Penelitian Internal"),
     *                  @OA\Property(property="approved_funds", type="integer", example=2000000),
     *                  @OA\Property(property="proposed_year", type="string", example="2024"),
     *                  @OA\Property(property="implementation_year", type="string", example="2024"),
     *                  @OA\Property(property="focus_area", type="string", example="Penelitian"),
     *                  @OA\Property(property="funded_institution_name", type="string", example="Sekolah Tinggi Manajemen Informatika dan Komputer Sinar Nusantara"),
     *                  @OA\Property(property="grant_program", type="string", example="Program Pengembangan"),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated access",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     * )
     */
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'leader_name' => 'required|string',
            'leaders_nidn' => 'required|string',
            'leaders_institution' => 'required|string',
            'title' => 'required|string',
            'scheme_short_name' => 'required|string',
            'scheme_name' => 'required|string',
            'approved_funds' => 'required|integer',
            'proposed_year' => 'required|string',
            'implementation_year' => 'required|string',
            'focus_area' => 'required|string',
            'funded_institution_name' => 'required|string',
            'grant_program' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->formatValidationErrors($validator);
        }

        $research = Research::create($request->all());

        return $this->successResponse($research, 'Research data created successfully.', 201);
    }

    /**
     * @OA\Patch(
     *     path="/api/researches/{id}",
     *     tags={"Research"},
     *     summary="Update an research by ID",
     *     security={{"bearer_token":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the research",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                 property="leader_name",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="leaders_nidn",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="leaders_institution",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="title",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="scheme_short_name",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="scheme_name",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="approved_funds",
     *                 type="integer"
     *             ),
     *             @OA\Property(
     *                 property="proposed_year",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="implementation_year",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="focus_area",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="funded_institution_name",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="grant_program",
     *                 type="string"
     *             ),
     *             example={"leader_name": "Yustina", "leaders_nidn": "2342453", "leaders_institution": "Sekolah Tinggi Manajemen Informatika dan Komputer Sinar Nusantara", "title": "Penelitian 1", "scheme_short_name": "PI", "scheme_name": "Penelitian Internal", "approved_funds": 2000000, "proposed_year": "2024", "implementation_year": "2024", "focus_area": "Penelitian", "funded_institution_name": "Sekolah Tinggi Manajemen Informatika dan Komputer Sinar Nusantara", "grant_program": "Program Pengembangan"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Author Updated",
     *          @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Research created successfully."),
     *             @OA\Property(property="data", type="object",
     *                  @OA\Property(property="id", type="integer", example=1),
     *                  @OA\Property(property="leader_name", type="string", example="Yustina"),
     *                  @OA\Property(property="leaders_nidn", type="string", example="2342453"),
     *                  @OA\Property(property="leaders_institution", type="string", example="Sekolah Tinggi Manajemen Informatika dan Komputer Sinar Nusantara"),
     *                  @OA\Property(property="title", type="string", example="Penelitian 1"),
     *                  @OA\Property(property="scheme_short_name", type="string", example="PI"),
     *                  @OA\Property(property="scheme_name", type="string", example="Penelitian Internal"),
     *                  @OA\Property(property="approved_funds", type="integer", example=2000000),
     *                  @OA\Property(property="proposed_year", type="string", example="2024"),
     *                  @OA\Property(property="implementation_year", type="string", example="2024"),
     *                  @OA\Property(property="focus_area", type="string", example="Penelitian"),
     *                  @OA\Property(property="funded_institution_name", type="string", example="Sekolah Tinggi Manajemen Informatika dan Komputer Sinar Nusantara"),
     *                  @OA\Property(property="grant_program", type="string", example="Program Pengembangan"),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated access",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $research = Research::find($id);
        if (!$research) {
            return response()->json([
                'success' => false,
                'message' => 'Research data not found.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'leader_name' => 'required|string',
            'leaders_nidn' => 'required|string',
            'leaders_institution' => 'required|string',
            'title' => 'required|string',
            'scheme_short_name' => 'required|string',
            'scheme_name' => 'required|string',
            'approved_funds' => 'required|integer',
            'proposed_year' => 'required|string',
            'implementation_year' => 'required|string',
            'focus_area' => 'required|string',
            'funded_institution_name' => 'required|string',
            'grant_program' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->formatValidationErrors($validator);
        }

        $research->update($request->all());

        return $this->successResponse($research, 'Research data updated successfully.', 200);
    }

    /**
     * @OA\Get(
     *     path="/api/researches",
     *     summary="Get paginated list of researches",
     *     description="Returns paginated researches with optional search functionality",
     *     operationId="getResearches",
     *     security={{"bearer_token": {}}},
     *     tags={"Research"},
     *     @OA\Parameter(
     *         name="q",
     *         in="query",
     *         description="Search term for filtering researches by title or leader name",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Research data retrieved successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="title", type="string", example="Research Title"),
     *                         @OA\Property(property="leader_name", type="string", example="John Doe"),
     *                         @OA\Property(property="leaders_nidn", type="string", example="1234567890"),
     *                         @OA\Property(property="leaders_institution", type="string", example="University"),
     *                         @OA\Property(property="scheme_short_name", type="string", example="RSH"),
     *                         @OA\Property(property="scheme_name", type="string", example="Research Scheme"),
     *                         @OA\Property(property="approved_funds", type="number", example=50000000),
     *                         @OA\Property(property="proposed_year", type="integer", example=2023),
     *                         @OA\Property(property="implementation_year", type="integer", example=2024),
     *                         @OA\Property(property="focus_area", type="string", example="Technology"),
     *                         @OA\Property(property="funded_institution_name", type="string", example="Funding Body"),
     *                         @OA\Property(property="grant_program", type="string", example="Research Grant"),
     *                         @OA\Property(
     *                             property="authors",
     *                             type="array",
     *                             @OA\Items(
     *                                 type="object",
     *                                 @OA\Property(property="id", type="integer", example=1),
     *                                 @OA\Property(property="name", type="string", example="Author Name"),
     *                                 @OA\Property(property="nidn", type="string", example="1234567890")
     *                             )
     *                         )
     *                     )
     *                 ),
     *                 @OA\Property(property="first_page_url", type="string"),
     *                 @OA\Property(property="from", type="integer"),
     *                 @OA\Property(property="last_page", type="integer"),
     *                 @OA\Property(property="last_page_url", type="string"),
     *                 @OA\Property(property="next_page_url", type="string"),
     *                 @OA\Property(property="path", type="string"),
     *                 @OA\Property(property="per_page", type="integer", example=10),
     *                 @OA\Property(property="prev_page_url", type="string"),
     *                 @OA\Property(property="to", type="integer"),
     *                 @OA\Property(property="total", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated access",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function getResearches()
    {
        $query = Research::query();

        if (request()->has('q')) {
            $search_term = request()->input('q');
            $query->whereAny(['title', 'leader_name'], 'like', '%' . $search_term . '%');
        }

        $researches = $query->with(['authors'])->paginate(10);

        return $this->successResponse($researches, 'Research data retrieved successfully.', 200);
    }

    /**
     * @OA\Get(
     *     path="/api/researches/{id}",
     *     summary="Get research by ID",
     *     description="Returns a specific research by its ID with related authors",
     *     operationId="getResearchById",
     *     security={{"bearer_token": {}}},
     *     tags={"Research"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of research to return",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Research data retrieved successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="Research Title"),
     *                 @OA\Property(property="leader_name", type="string", example="John Doe"),
     *                 @OA\Property(property="leaders_nidn", type="string", example="1234567890"),
     *                 @OA\Property(property="leaders_institution", type="string", example="University"),
     *                 @OA\Property(property="scheme_short_name", type="string", example="RSH"),
     *                 @OA\Property(property="scheme_name", type="string", example="Research Scheme"),
     *                 @OA\Property(property="approved_funds", type="number", example=50000000),
     *                 @OA\Property(property="proposed_year", type="integer", example=2023),
     *                 @OA\Property(property="implementation_year", type="integer", example=2024),
     *                 @OA\Property(property="focus_area", type="string", example="Technology"),
     *                 @OA\Property(property="funded_institution_name", type="string", example="Funding Body"),
     *                 @OA\Property(property="grant_program", type="string", example="Research Grant"),
     *                 @OA\Property(
     *                     property="authors",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                        @OA\Property(property="id", type="integer", example=1),
     *                  @OA\Property(property="sinta_id", type="string", example="34514545"),
     *                  @OA\Property(property="nidn", type="string", example="2342453"),
     *                  @OA\Property(property="name", type="string", example="Yustina"),
     *                  @OA\Property(property="affiliation", type="string", example="Sekolah Tinggi Manajemen Informatika dan Komputer Sinar Nusantara"),
     *                  @OA\Property(property="study_program_id", type="integer", example=3),
     *                  @OA\Property(property="last_education", type="string", example="S1"),
     *                  @OA\Property(property="functional_position", type="string", example="Lektor"),
     *                  @OA\Property(property="title_prefix", type="string", example="Prof."),
     *                  @OA\Property(property="title_suffix", type="string", example="S.T."),
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Research not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Research data not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated access",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */

    public function getResearchByID($id)
    {
        $research = Research::with('authors')->find($id);
        if (!$research) {
            return response()->json([
                'success' => false,
                'message' => 'Research data not found.',
            ], 404);
        }

        return $this->successResponse($research, 'Research data retrieved successfully.', 200);
    }

    public function getResearchesGroupedByScheme()
    {
        $query = Research::with('authors');

        if (request()->has('study_program_id')) {
            $study_program_id = request()->input('study_program_id');
            $query->whereHas('authors', function ($q) use ($study_program_id) {
                $q->where('study_program_id', $study_program_id);
            });
        }

        $researches = $query->get();
        $grouped_data = $researches->groupBy('scheme_short_name')->map(function ($group) {
            return [
                'count' => $group->count(),
                'data' => $group
            ];
        });

        return $this->successResponse($grouped_data, 'Research data retrieved successfully.', 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/researches/{id}",
     *     summary="Delete a research",
     *     description="Deletes a research record by ID",
     *     operationId="deleteResearch",
     *     security={{"bearer_token": {}}},
     *     tags={"Research"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of research to delete",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Research deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Response data deleted successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Research not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Research data not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated access",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function delete($id)
    {
        $research = Research::find($id);
        if (!$research) {
            return response()->json([
                'success' => false,
                'message' => 'Research data not found.'
            ], 404);
        }

        $research->delete();

        return $this->successResponse(null, 'Research data deleted successfully.', 200);
    }

}
