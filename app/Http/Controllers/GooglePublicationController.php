<?php

namespace App\Http\Controllers;

use App\Imports\GoogleImport;
use App\Models\Author;
use App\Models\GooglePublication;
use App\Models\StudyProgram;
use App\Traits\ApiResponse;
use App\Traits\FunctionalMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class GooglePublicationController extends Controller
{
    use ApiResponse, FunctionalMethod;


    public function __construct()
    {
        $this->middleware('role:superadmin|admin');
    }

    /**
     * @OA\Post(
     *     path="/api/google-publications/import",
     *     summary="Import google publications data from Excel/CSV file",
     *     tags={"Google Publications"},
     *     security={{ "bearer": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="file",
     *                     type="file",
     *                     description="Excel/CSV file containing google publications data"
     *                 ),
     *                 @OA\Property(
     *                     property="reset_table",
     *                     type="boolean",
     *                     description="Whether to reset the tables before import",
     *                     example=false
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Google Publications imported successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Google Publications data imported successfully."),
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error or import failure",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 422);
        }

        try {
            Excel::import(new GoogleImport(), $request->file('file'));

            return $this->successResponse(null, 'Google Publication data imported successfully.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/google-publications",
     *     tags={"Google Publications"},
     *     summary="Create new google publication",
     *     security={{"bearer_token":{}}},
     *  @OA\RequestBody(
     *     required=true,
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *             @OA\Property(
     *                 property="accreditation",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="title",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="journal",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="year",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="citation",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="authors",
     *                 type="array",
     *                 @OA\Items(
     *                     type="integer",
     *                     example=2
     *                 )
     *             ),
     *             example={"accreditation": "S3", "title": "Publikasi 1", "journal": "Journal of information", "year": "2024", "citation": "1", "authors": {"0": 2, "1": 4, "2": 8} }
     *         )
     *     )
     * ),
     *     @OA\Response(
     *         response=201,
     *         description="Google Publication Created",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Google publication data created successfully."),
     *             @OA\Property(property="data", type="object",
     *                  @OA\Property(property="id", type="integer", example=1),
     *                  @OA\Property(property="accreditation", type="string", example="S3"),
     *                  @OA\Property(property="title", type="string", example="Publikasi 1"),
     *                  @OA\Property(property="journal", type="string", example="Journal of information"),
     *                  @OA\Property(property="year", type="string", example="2024"),
     *                  @OA\Property(property="citation", type="string", example="1"),
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
            'accreditation' => 'required|string|max:50',
            'title' => 'required|string|max:255|unique:google_publications,title',
            'journal' => 'required|string|max:255',
            'year' => 'required|max:4',
            'citation' => 'required|string|max:10',
            'authors' => 'nullable|array',
            'authors.*' => 'nullable|exists:authors,id',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 422);
        }

        $authors = $request->authors;

        $creators = Author::whereIn('id', $authors)
            ->pluck('name')
            ->implode(', ');

        $request->merge(['creators' => $creators]);

        $data = GooglePublication::create($request->all());
        $data->authors()->attach($request->authors);
        $data->save();

        $data->load('authors');

        return $this->successResponse($data, 'Google Publication created successfully.', 201);
    }

    /**
     * @OA\Patch(
     *     path="/api/google-publications/{id}",
     *     tags={"Google Publications"},
     *     summary="Update an google publication by ID",
     *     security={{"bearer_token":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the google publication",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *             @OA\Property(
     *                 property="accreditation",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="title",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="journal",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="year",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="citation",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="authors",
     *                 type="array",
     *                     @OA\Items(
     *                     type="integer",
     *                     example=2
     *                 )
     *             ),
     *             example={"accreditation": "S3", "title": "Publikasi 1", "journal": "Journal of information", "year": "2024", "citation": "1", "authors": {"0": 2, "1": 4, "2": 8} }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Google Publication Created",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Google publication data created successfully."),
     *             @OA\Property(property="data", type="object",
     *                  @OA\Property(property="id", type="integer", example=1),
     *                  @OA\Property(property="accreditation", type="string", example="S3"),
     *                  @OA\Property(property="title", type="string", example="Publikasi 1"),
     *                  @OA\Property(property="journal", type="string", example="Journal of information"),
     *                  @OA\Property(property="year", type="string", example="2024"),
     *                  @OA\Property(property="citation", type="string", example="1"),
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
        $validator = Validator::make($request->all(), [
            'accreditation' => 'required|string|max:50',
            'title' => 'required|string|max:255|unique:google_publications,title,' . $id,
            'journal' => 'required|string|max:255',
            'year' => 'required|max:4',
            'citation' => 'required|string|max:10',
            'authors' => 'nullable|array',
            'authors.*' => 'nullable|exists:authors,id',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 422);
        }

        $data = GooglePublication::find($id);
        if (!$data) {
            return $this->errorResponse('Google Publication not found.', 404);
        }

        $authors = $request->authors;

        $creators = Author::whereIn('id', $authors)
            ->pluck('name')
            ->implode(', ');

        $request->merge(['creators' => $creators]);

        $data->update($request->all());

        $data->authors()->sync($request->authors);
        $data->save();

        $data->load('authors');

        return $this->successResponse($data, 'Google Publication updated successfully.', 200);
    }

    /**
     * @OA\Get(
     *     path="/api/google-publications",
     *     summary="Get paginated list of google publications",
     *     description="Returns paginated google publications with optional search functionality",
     *     security={{"bearer_token": {}}},
     *     tags={"Google Publications"},
     *     @OA\Parameter(
     *         name="q",
     *         in="query",
     *         description="Search term for filtering data by title, journal or creators",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Google publication data retrieved successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                        @OA\Property(property="id", type="integer", example=1),
     *                        @OA\Property(property="accreditation", type="string", example="S3"),
     *                        @OA\Property(property="title", type="string", example="Publikasi 1"),
     *                        @OA\Property(property="journal", type="string", example="Journal of information"),
     *                        @OA\Property(property="year", type="string", example="2024"),
     *                        @OA\Property(property="citation", type="string", example="1"),
     *                         @OA\Property(
     *                             property="authors",
     *                             type="array",
     *                             @OA\Items(
     *                                 type="object",
     *                                 @OA\Property(property="id", type="integer", example=1),
     *                                 @OA\Property(property="sinta_id", type="string", example="66889756"),
     *                                 @OA\Property(property="nidn", type="string", example="2342453"),
     *                                 @OA\Property(property="name", type="string", example="Yustina"),
     *                                 @OA\Property(property="affiliation", type="string", example="Sekolah Tinggi Manajemen Informatika dan Komputer Sinar Nusantara"),
     *                                 @OA\Property(property="study_program_id", type="integer", example="2"),
     *                                 @OA\Property(property="last_education", type="string", example="S2"),
     *                                 @OA\Property(property="functional_position", type="string", example="Asisten Ahli"),
     *                                 @OA\Property(property="title_prefix", type="string", example=null),
     *                                 @OA\Property(property="title_suffix", type="string", example="S.Kom, M.Kom"),
     *                             ),
     *                         )
     *                     )
     *                 ),
     *                 @OA\Property(
     *                      property="meta",
     *                      type="object",
     *                          @OA\Property(property="total", type="integer", example=100),   
     *                          @OA\Property(property="per_page", type="integer", example=10),
     *                          @OA\Property(property="current_page", type="integer", example=1),
     *                          @OA\Property(property="last_page", type="integer", example=10),
     *                  )
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
    public function getGooglePublications()
    {
        $query = GooglePublication::query();

        if (request()->has('q')) {
            $search_term = request()->input('q');
            $query->whereAny(['title', 'journal', 'creators'], 'like', "%$search_term%");
        }

        $data = $query->with('authors')->paginate(10);

        return $this->paginatedResponse($data, 'Google Publications retrieved successfully.', 200);
    }

    /**
     * @OA\Get(
     *     path="/api/google-publications/{id}",
     *     summary="Get google publication by ID",
     *     description="Returns a specific google publication by its ID with related authors",
     *     security={{"bearer_token": {}}},
     *     tags={"Google Publications"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of google publication to return",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Google publication data retrieved successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="accreditation", type="string", example="S3"),
     *                 @OA\Property(property="title", type="string", example="Publikasi 1"),
     *                 @OA\Property(property="journal", type="string", example="Journal of information"),
     *                 @OA\Property(property="year", type="string", example="2024"),
     *                 @OA\Property(property="citation", type="string", example="1"),
     *                 @OA\Property(
     *                     property="authors",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                        @OA\Property(property="id", type="integer", example=1),
     *                        @OA\Property(property="sinta_id", type="string", example="34514545"),
     *                        @OA\Property(property="nidn", type="string", example="2342453"),
     *                        @OA\Property(property="name", type="string", example="Yustina"),
     *                        @OA\Property(property="affiliation", type="string", example="Sekolah Tinggi Manajemen Informatika dan Komputer Sinar Nusantara"),
     *                        @OA\Property(property="study_program_id", type="integer", example=3),
     *                        @OA\Property(property="last_education", type="string", example="S1"),
     *                        @OA\Property(property="functional_position", type="string", example="Lektor"),
     *                        @OA\Property(property="title_prefix", type="string", example="Prof."),
     *                        @OA\Property(property="title_suffix", type="string", example="S.T."),
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Data not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Data data not found.")
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
    public function getGooglePublicationByID($id)
    {
        $data = GooglePublication::with('authors')->find($id);
        if (!$data) {
            return $this->errorResponse('Google Publication not found.', 404);
        }

        return $this->successResponse($data, 'Google Publication retrieved successfully.', 200);
    }

    /**
     * @OA\Get(
     *     path="/api/google-publications/grouped-by-scheme",
     *     summary="Get google publications grouped by scheme",
     *     security={{"bearer_token": {}}},
     *     description="Retrieves google publications data grouped by `accreditation`, with an optional filter by `study_program_id`.",
     *     tags={"Google Publications"},
     *     @OA\Parameter(
     *         name="study_program_id",
     *         in="query",
     *         description="Filter google publications by study program ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful retrieval of google publications data",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Google publications data retrieved successfully."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="S3", type="object",
     *                     @OA\Property(property="count", type="integer", example=2),
     *                 )
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
    public function getDataGroupedByAccreditation()
    {
        $query = GooglePublication::with('authors');

        if (request()->has('study_program_id')) {
            $study_program_id = request()->input('study_program_id');
            $query->whereHas('authors', function ($q) use ($study_program_id) {
                $q->where('study_program_id', $study_program_id);
            });
        }

        $data = $query->get();

        $grouped_data = $data->groupBy('accreditation')->map(function ($group) {
            return [
                'count' => $group->count(),
            ];
        });

        return $this->successResponse($grouped_data, 'Google Publications grouped by accreditation retrieved successfully.', 200);
    }

    /**
     * @OA\Get(
     *     path="/api/google-publications/chart-data",
     *     summary="Get google publications statistics chart data",
     *     security={{"bearer_token": {}}},
     *     description="Retrieves google publications statistics grouped by study programs for chart visualization",
     *     tags={"Google Publications"},
     *     @OA\Parameter(
     *         name="year",
     *         in="query",
     *         description="Filter statistics by year",
     *         required=false,
     *         @OA\Schema(type="string", example="2024")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Google publications chart data retrieved successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="labels",
     *                     type="array",
     *                     @OA\Items(type="string", example="S1-Informatika")
     *                 ),
     *                 @OA\Property(
     *                     property="datasets",
     *                     type="object",
     *                     @OA\Property(
     *                         property="data",
     *                         type="array",
     *                         @OA\Items(type="integer", example=10)
     *                     ),
     *                     @OA\Property(
     *                         property="background_color",
     *                         type="array",
     *                         @OA\Items(type="string", example="#FF5733")
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="study_programs",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="name", type="string", example="S1-Informatika"),
     *                         @OA\Property(property="total", type="integer", example=10),
     *                         @OA\Property(property="percentage", type="number", format="float", example=25.5)
     *                     )
     *                 ),
     *                 @OA\Property(property="total_data", type="integer", example=40)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function getChartsData()
    {
        // Base query starting with study programs
        $data_by_program = StudyProgram::select(
            'study_programs.name as study_program',
            DB::raw('COALESCE(COUNT(DISTINCT google_publications.id), 0) as total_publications')
        )
            ->leftJoin('authors', 'study_programs.id', '=', 'authors.study_program_id')
            ->leftJoin('author_google_publication', 'authors.id', '=', 'author_google_publication.author_id')
            ->leftJoin('google_publications', 'author_google_publication.google_publication_id', '=', 'google_publications.id');

        // Apply year filter if provided
        if (request()->has('year')) {
            $year = request()->input('year');
            $data_by_program->where('year', $year);
        }

        // Complete the query and fetch the data
        $data_by_program = $data_by_program
            ->groupBy('study_programs.id', 'study_programs.name')
            ->orderBy('study_programs.name')
            ->get();

        // Calculate total data
        $total_data = $data_by_program->sum('total_publications');

        // Prepare chart data
        $chart_data = [
            'labels' => $data_by_program->pluck('study_program')->toArray(),
            'datasets' => [
                'data' => $data_by_program->pluck('total_publications')->toArray(),
                'background_color' => $this->generateColors(count($data_by_program)),
            ],
            'study_programs' => $data_by_program->map(function ($item) use ($total_data) {
                return [
                    'name' => $item->study_program,
                    'total' => $item->total_publications,
                    'percentage' => $total_data > 0 ? round(($item->total_publications / $total_data) * 100, 2) : 0,
                ];
            }),
            'total_data' => $total_data,
        ];

        return $this->successResponse($chart_data, 'Chart data retrieved successfully.', 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/google-publications/{id}",
     *     summary="Delete a google publication",
     *     description="Deletes a google publication record by ID",
     *     security={{"bearer_token": {}}},
     *     tags={"Google Publications"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of google publication to delete",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Google publication deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Google publication data deleted successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Google publication not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Google publication data not found.")
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
        $data = GooglePublication::find($id);
        if (!$data) {
            return $this->errorResponse('Google Publication not found.', 404);
        }

        $data->delete();

        return $this->successResponse(null, 'Google Publication deleted successfully.', 200);
    }
}
