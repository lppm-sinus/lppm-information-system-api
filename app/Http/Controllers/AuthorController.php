<?php

namespace App\Http\Controllers;

use App\Imports\AuthorImport;
use App\Models\Author;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;

class AuthorController extends Controller
{
    public function __construct()
    {
        $this->middleware(['role:superadmin']);
    }

    /**
     * @OA\Post(
     *     path="/api/authors/import",
     *     tags={"Authors"},
     *     summary="Import authors from excel file",
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
     *         description="Author Data Imported",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Authors data imported successfully."),
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Author with NIDN 345525656 already exists."),
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated access",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized Access",
     *         @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Unauthorized"),
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

        if ($request->boolean('reset_table')) {
            Author::truncate();
        }

        try {
            Excel::import(new AuthorImport, $request->file('file'));
            return response()->json([
                'success' => true,
                'message' => 'Authors data imported successfully',
            ], 201);
        } catch (\Exception $e) {
            return response()->json(json_decode($e->getMessage()), 422);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/authors",
     *     tags={"Authors"},
     *     summary="Create new author",
     *     security={{"bearer_token":{}}},
     *  @OA\RequestBody(
     *     required=true,
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *             @OA\Property(
     *                 property="sinta_id",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="nidn",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="name",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="affiliation",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="study_program_id",
     *                 type="integer"
     *             ),
     *             @OA\Property(
     *                 property="last_education",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="functional_position",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="title_prefix",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="title_suffix",
     *                 type="string"
     *             ),
     *             example={"sinta_id": "34514545", "nidn": "2342453", "name": "Yustina", "affiliation": "Sekolah Tinggi Manajemen Informatika dan Komputer Sinar Nusantara", "study_program_id": 3, "last_education": "S1", "functional_position": "Lektor", "title_prefix": "Prof.", "title_suffix": "S.T."}
     *         )
     *     )
     * ),
     *     @OA\Response(
     *         response=201,
     *         description="Author Created",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Author created successfully."),
     *             @OA\Property(property="data", type="object",
     *                  @OA\Property(property="id", type="integer", example=1),
     *                  @OA\Property(property="sinta_id", type="string", example="34514545"),
     *                  @OA\Property(property="nidn", type="string", example="2342453"),
     *                  @OA\Property(property="name", type="string", example="Yustina"),
     *                  @OA\Property(property="affiliation", type="string", example="Sekolah Tinggi Manajemen Informatika dan Komputer Sinar Nusantara"),
     *                  @OA\Property(property="study_program_id", type="integer", example=3),
     *                  @OA\Property(property="last_education", type="string", example="S1"),
     *                  @OA\Property(property="functional_position", type="string", example="Lektor"),
     *                  @OA\Property(property="title_prefix", type="string", example="Prof."),
     *                  @OA\Property(property="title_suffix", type="string", example="S.T."),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="The name field is required."),
     *              @OA\Property(
     *                  property="errors", type="object",
     *                  @OA\Property(
     *                      property="title", type="array",
     *                      @OA\Items(
     *                          type="string", example="The name field is required."
     *                      )
     *                  )
     *              )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated access",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized Access",
     *         @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Unauthorized"),
     *         )
     *     ),
     * )
     */
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sinta_id' => 'required',
            'nidn' => 'required|unique:authors,nidn',
            'name' => 'required',
            'affiliation' => 'required',
            'study_program_id' => 'required|exists:study_programs,id',
            'last_education' => 'required',
            'functional_position' => 'required',
            'title_prefix' => 'nullable',
            'title_suffix' => 'nullable',
        ]);

        if ($validator->fails()) {
            return $this->formatValidationErrors($validator);
        }

        $author = Author::create([
            'sinta_id' => $request->sinta_id,
            'nidn' => $request->nidn,
            'name' => $request->name,
            'affiliation' => $request->affiliation,
            'study_program_id' => $request->study_program_id,
            'last_education' => $request->last_education,
            'functional_position' => $request->functional_position,
            'title_prefix' => $request->title_prefix,
            'title_suffix' => $request->title_suffix,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Author created successfully.',
            'data' => $author
        ], 201);
    }

    /**
     * @OA\Patch(
     *     path="/api/authors/{id}",
     *     tags={"Authors"},
     *     summary="Update an author by ID",
     *     security={{"bearer_token":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the author",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                 property="sinta_id",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="nidn",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="name",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="affiliation",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="study_program_id",
     *                 type="integer"
     *             ),
     *             @OA\Property(
     *                 property="last_education",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="functional_position",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="title_prefix",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="title_suffix",
     *                 type="string"
     *             ),
     *             example={"sinta_id": "34514545", "nidn": "2342453", "name": "Yustina", "affiliation": "Sekolah Tinggi Manajemen Informatika dan Komputer Sinar Nusantara", "study_program_id": 3, "last_education": "S1", "functional_position": "Lektor", "title_prefix": "Prof.", "title_suffix": "S.T."}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Author Updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Author successfully updated."),
     *             @OA\Property(property="data", type="object",
     *                  @OA\Property(property="id", type="integer", example=1),
     *                  @OA\Property(property="sinta_id", type="string", example="34514545"),
     *                  @OA\Property(property="nidn", type="string", example="2342453"),
     *                  @OA\Property(property="name", type="string", example="Yustina"),
     *                  @OA\Property(property="affiliation", type="string", example="Sekolah Tinggi Manajemen Informatika dan Komputer Sinar Nusantara"),
     *                  @OA\Property(property="study_program_id", type="integer", example=3),
     *                  @OA\Property(property="last_education", type="string", example="S1"),
     *                  @OA\Property(property="functional_position", type="string", example="Lektor"),
     *                  @OA\Property(property="title_prefix", type="string", example="Prof."),
     *                  @OA\Property(property="title_suffix", type="string", example="S.T."),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="The name field is required."),
     *              @OA\Property(
     *                  property="errors", type="object",
     *                  @OA\Property(
     *                      property="title", type="array",
     *                      @OA\Items(
     *                          type="string", example="The name field is required."
     *                      )
     *                  ),
     *              )
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
        $author = Author::find($id);
        if (!$author) {
            return response()->json([
                'success' => false,
                'message' => 'Author not found.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'sinta_id' => 'required',
            'nidn' => 'required|unique:authors,nidn,' . $id,
            'name' => 'required',
            'affiliation' => 'required',
            'study_program_id' => 'required|exists:study_programs,id',
            'last_education' => 'required',
            'functional_position' => 'required',
            'title_prefix' => 'nullable',
            'title_suffix' => 'nullable',
        ]);

        if ($validator->fails()) {
            return $this->formatValidationErrors($validator);
        }

        $author->update([
            'sinta_id' => $request->sinta_id,
            'nidn' => $request->nidn,
            'name' => $request->name,
            'affiliation' => $request->affiliation,
            'study_program_id' => $request->study_program_id,
            'last_education' => $request->last_education,
            'functional_position' => $request->functional_position,
            'title_prefix' => $request->title_prefix,
            'title_suffix' => $request->title_suffix,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Author updated successfully.',
            'data' => $author
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/authors/{id}",
     *     tags={"Authors"},
     *     summary="Get author by ID",
     *     security={{"bearer_token":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the author",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Author data retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Author data retrieved successfully."),
     *             @OA\Property(property="data", type="object",
     *                  @OA\Property(property="id", type="integer", example=1),
     *                  @OA\Property(property="sinta_id", type="string", example="5975479"),
     *                  @OA\Property(property="nidn", type="string", example="0617057603"),
     *                  @OA\Property(property="name", type="string", example="TEGUH SUSYANTO"),
     *                  @OA\Property(property="affiliation", type="string", example="Sekolah Tinggi Manajemen Informatika dan Komputer Sinar Nusantara"),
     *                  @OA\Property(property="study_program_id", type="integer", example=1),
     *                  @OA\Property(property="last_education", type="string", example="S2"),
     *                  @OA\Property(property="functional_position", type="string", example="Lektor"),
     *                  @OA\Property(property="title_prefix", type="string", example=null),
     *                  @OA\Property(property="title_suffix", type="string", example="S.Kom, M.Cs"),
     *                  @OA\Property(property="created_at", type="string", format="date-time", example="2024-10-29T06:00:16.000000Z"),
     *                  @OA\Property(property="updated_at", type="string", format="date-time", example="2024-10-29T06:00:16.000000Z"),
     *                  @OA\Property(
     *                      property="study_program",
     *                      type="object",
     *                      @OA\Property(property="id", type="integer", example=1),
     *                      @OA\Property(property="name", type="string", example="S1 Sistem Informasi"),
     *                      @OA\Property(property="created_at", type="string", format="date-time", example="2024-10-23T06:09:07.000000Z"),
     *                      @OA\Property(property="updated_at", type="string", format="date-time", example="2024-10-23T06:09:07.000000Z")
     *                  )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Author not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Author not found.")
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
    public function getAuthorByID($id)
    {
        $author = Author::find($id);
        if (!$author) {
            return response()->json([
                'success' => false,
                'message' => 'Author not found.'
            ], 404);
        }

        $author->study_program = $author->studyProgram;
        return response()->json([
            'success' => true,
            'message' => 'Author data retrieved successfully.',
            'data' => $author
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/authors",
     *     tags={"Authors"},
     *     summary="Get authors data",
     *     security={{"bearer_token":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Get authors data successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Authors data retrieved successfully."),
     *             @OA\Property(property="data", type="object",
     *                  @OA\Property(property="current_page", type="integer", example=1),
     *                  @OA\Property(property="data", 
     *                      type="array",
     *                      @OA\Items(
     *                          type="object",
     *                          @OA\Property(property="id", type="integer", example=1),
     *                          @OA\Property(property="sinta_id", type="string", example="5975479"),
     *                          @OA\Property(property="nidn", type="string", example="0617057603"), 
     *                          @OA\Property(property="name", type="string", example="TEGUH SUSYANTO"),
     *                          @OA\Property(property="affiliation", type="string", example="Sekolah Tinggi Manajemen Informatika dan Komputer Sinar Nusantara"),
     *                          @OA\Property(property="study_program_id", type="integer", example=1),
     *                          @OA\Property(property="last_education", type="string", example="S2"),
     *                          @OA\Property(property="functional_position", type="string", example="Lektor"),
     *                          @OA\Property(property="title_prefix", type="string", example=null),
     *                          @OA\Property(property="title_suffix", type="string", example="S.Kom, M.Cs"),
     *                          @OA\Property(property="created_at", type="string", format="date-time", example="2024-09-12T06:33:25.000000Z"),
     *                          @OA\Property(property="updated_at", type="string", format="date-time", example="2024-09-12T06:33:25.000000Z"),
     *                          @OA\Property(
     *                              property="study_program",
     *                              type="object",
     *                              @OA\Property(property="id", type="integer", example=1),
     *                              @OA\Property(property="name", type="string", example="S1 Sistem Informasi"),
     *                              @OA\Property(property="created_at", type="string", format="date-time", example="2024-10-23T06:09:07.000000Z"), 
     *                              @OA\Property(property="updated_at", type="string", format="date-time", example="2024-10-23T06:09:07.000000Z")
     *                          )
     *                      ),
     *                       @OA\Items(
     *                          type="object",
     *                          @OA\Property(property="id", type="integer", example=1),
     *                          @OA\Property(property="sinta_id", type="string", example="321333435"),
     *                          @OA\Property(property="nidn", type="string", example="465424234"), 
     *                          @OA\Property(property="name", type="string", example="Sapto Nug"),
     *                          @OA\Property(property="affiliation", type="string", example="Sekolah Tinggi Manajemen Informatika dan Komputer Sinar Nusantara"),
     *                          @OA\Property(property="study_program_id", type="integer", example=3),
     *                          @OA\Property(property="last_education", type="string", example="S2"),
     *                          @OA\Property(property="functional_position", type="string", example="Lektor"),
     *                          @OA\Property(property="title_prefix", type="string", example=null),
     *                          @OA\Property(property="title_suffix", type="string", example="S.Kom, M.Kom"),
     *                          @OA\Property(property="created_at", type="string", format="date-time", example="2024-09-12T06:33:25.000000Z"),
     *                          @OA\Property(property="updated_at", type="string", format="date-time", example="2024-09-12T06:33:25.000000Z"),
     *                          @OA\Property(
     *                              property="study_program",
     *                              type="object",
     *                              @OA\Property(property="id", type="integer", example=1),
     *                              @OA\Property(property="name", type="string", example="S1 Sistem Informasi"),
     *                              @OA\Property(property="created_at", type="string", format="date-time", example="2024-10-23T06:09:07.000000Z"), 
     *                              @OA\Property(property="updated_at", type="string", format="date-time", example="2024-10-23T06:09:07.000000Z")
     *                          )
     *                      )
     *                  ),
     *                  @OA\Property(property="first_page_url", type="string", example="http://localhost:8000/api/authors?page=1"),
     *                  @OA\Property(property="from", type="integer", example=1),
     *                  @OA\Property(property="last_page", type="integer", example=2),
     *                  @OA\Property(property="last_page_url", type="string", example="http://localhost:8000/api/authors?page=2"),
     *                  @OA\Property(property="links", 
     *                      type="array", 
     *                      example={{ 
     *                          "url": null,
     *                          "label": "&laquo; Previous",
     *                          "active": false
     *                      }, {
     *                          "url": "http://localhost:8000/api/authors?page=1",
     *                          "label": "1",
     *                          "active": true
     *                      }, {
     *                          "url": null,
     *                          "label": "Next &raquo;",
     *                          "active": false
     *                      }},
     *                      @OA\Items(
     *                          type="object",
     *                          @OA\Property(property="url", type="string", example=null),
     *                          @OA\Property(property="label", type="string", example="&laquo; Previous"),
     *                          @OA\Property(property="active", type="boolean", example=false),
     *                      ),
     *                  ),
     *                  @OA\Property(property="next_page_url", type="string", example=null),
     *                  @OA\Property(property="path", type="string", example="http://localhost:8000/api/authors"),
     *                  @OA\Property(property="per_page", type="integer", example=10),
     *                  @OA\Property(property="prev_page", type="string", example=null),
     *                  @OA\Property(property="to", type="integer", example=4),
     *                  @OA\Property(property="total", type="integer", example=4)
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
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized Access",
     *         @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Unauthorized."),
     *         )
     *     ),
     * )
     */
    public function getAuthors()
    {
        $query = Author::query();

        if (request()->has('search')) {
            $search_term = request()->input('search');
            $query->where('name', 'like', '%' . $search_term . '%')
                ->orWhere('sinta_id', 'like', '%' . $search_term . '%')
                ->orWhere('nidn', 'like', '%' . $search_term . '%');
        }

        $authors = $query->with('studyProgram')->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Authors data retrieved successfully.',
            'data' => $authors
        ], 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/authors/{id}",
     *     tags={"Authors"},
     *     summary="Delete a author by ID",
     *     security={{"bearer_token":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the author",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Author data deleted successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Author data deleted successfully."),
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated access",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Author not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="string", example=false),
     *             @OA\Property(property="message", type="string", example="Author not found."),
     *         )
     *     )
     * )
     */
    public function delete($id)
    {
        $author = Author::find($id);
        if (!$author) {
            return response()->json([
                'success' => false,
                'message' => 'Author not found.',
            ], 404);
        }

        $author->delete();

        return response()->json([
            'success' => true,
            'message' => 'Author deleted successfully.'
        ], 200);
    }

    public function formatValidationErrors($validator)
    {
        return response()->json([
            'success' => false,
            'errors' => $validator->errors(),
        ], 422);
    }
}
