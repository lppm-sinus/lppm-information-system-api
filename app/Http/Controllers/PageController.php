<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Cviebrock\EloquentSluggable\Services\SlugService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PageController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/pages",
     *     tags={"Pages"},
     *     summary="Create new page",
     *     security={{"bearer_token":{}}},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="title",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="parent_id",
     *                     type="integer"
     *                 ),
     *                 example={"title": "Layanan", 
     *                          "parent_id": null}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Page Created",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Page created successfully."),
     *             @OA\Property(property="data", type="object",
     *                  @OA\Property(property="id", type="integer", example=1),
     *                  @OA\Property(property="title", type="string", example="Penelitian"),
     *                  @OA\Property(property="slug", type="string", example="penelitian"),
     *                  @OA\Property(property="link", type="string", example="/layanan/penelitian"),
     *                  @OA\Property(property="parent_id", type="integer", example=1),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="The name field must be at least 3 characters"),
     *              @OA\Property(
     *                  property="errors", type="object",
     *                  @OA\Property(
     *                      property="title", type="array",
     *                      @OA\Items(
     *                          type="string", example="The title field must be at least 3 characters."
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
        $authCheck = $this->checkIfSuperAdmin();
        if ($authCheck !== true) {
            return $authCheck;
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|min:3|max:100|string',
            'parent_id' => 'nullable|integer|exists:pages,id',
        ]);

        if ($validator->fails()) {
            return $this->formatValidationErrors($validator);
        }

        $slug = SlugService::createSlug(Page::class, 'slug', $request->title);

        $existingSlug = Page::where('slug', $slug)->first();

        if ($request->parent_id != null) {
            if ($existingSlug) {
                $parent_menu = Page::where('id', $request->parent_id)->first();
                $slug = $parent_menu->slug . '-' . $slug;
                $link = '/' . $parent_menu->slug . '/' . $slug;
            } else {
                $parent_menu = Page::where('id', $request->parent_id)->first();
                $link = '/' . $parent_menu->slug . '/' . $slug;
            }
        } else {
            if ($existingSlug) {
                return response()->json([
                    'success' => false,
                    'message' => 'Slug already exists. Please choose a different title.'
                ], 400);
            } else {
                $link = '/' . $slug;
            }
        }

        $page = Page::create([
            'title' => $request->title,
            'slug' => $slug,
            'link' => $link,
            'parent_id' => $request->parent_id
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Page created successfully.',
            'data' => $page
        ], 201);
    }

    /**
     * @OA\Patch(
     *     path="/api/pages/{id}",
     *     tags={"Pages"},
     *     summary="Update a page by ID",
     *     security={{"bearer_token":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the page",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="title",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="parent_id",
     *                     type="integer"
     *                 ),
     *                 example={"title": "My new updated page", "parent_id": null}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Page Updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Page successfully updated."),
     *             @OA\Property(property="data", type="object",
     *                  @OA\Property(property="id", type="integer", example=1),
     *                  @OA\Property(property="title", type="string", example="My new updated page"),
     *                  @OA\Property(property="slug", type="string", example="my-new-updated-page"),
     *                  @OA\Property(property="link", type="string", example="/my-new-updated-page"),
     *                  @OA\Property(property="parent_id", type="integer", example=null),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="The title field must be at least 3 characters"),
     *              @OA\Property(
     *                  property="errors", type="object",
     *                  @OA\Property(
     *                      property="title", type="array",
     *                      @OA\Items(
     *                          type="string", example="The title field must be at least 3 characters."
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
        $authCheck = $this->checkIfSuperAdmin();
        if ($authCheck !== true) {
            return $authCheck;
        }

        $page = Page::find($id);
        if (!$page) {
            return response()->json([
                'success' => false,
                'message' => 'Page not found.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|min:3|max:100|string',
            'parent_id' => 'nullable|integer|exists:pages,id'
        ]);

        if ($validator->fails()) {
            return $this->formatValidationErrors($validator);
        }

        $slug = SlugService::createSlug(Page::class, 'slug', $request->title);

        if ($request->parent_id != null) {
            $parent_menu = Page::where('id', $request->parent_id)->first();
            $link = '/' . $parent_menu->slug . '/' . $slug;
        } else {
            $link = '/' . $slug;
        }

        $existingSlug = Page::where('id', '!=', $id)->where('slug', $slug)->first();
        if ($existingSlug) {
            return response()->json([
                'success' => false,
                'message' => 'Slug already exists. Please choose a different title.'
            ], 400);
        }

        $page->title = $request->title;
        $page->parent_id = $request->parent_id;
        $page->slug = $slug;
        $page->link = $link;

        $page->save();

        return response()->json([
            'success' => true,
            'message' => 'Page updated successfully.',
            'data' => $page
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/pages/{id}",
     *     tags={"Pages"},
     *     summary="Get a page by ID",
     *     security={{"bearer_token":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the page",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Page data retrieved successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Page data retrieved successfully."),
     *             @OA\Property(property="data", type="object",
     *                  @OA\Property(property="id", type="integer", example=1),
     *                  @OA\Property(property="title", type="string", example="Penelitian"),
     *                  @OA\Property(property="slug", type="string", example="penelitian"),
     *                  @OA\Property(property="link", type="string", example="/layanan/penelitian"),
     *                  @OA\Property(property="parent_id", type="integer", example=3),
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
    public function getPageByID($id)
    {
        $authCheck = $this->checkIfSuperAdmin();
        if ($authCheck !== true) {
            return $authCheck;
        }

        $page = Page::find($id);
        if (!$page) {
            return response()->json([
                'success' => false,
                'message' => 'Page not found.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Page retrieved successfully.',
            'data' => $page
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/pages",
     *     tags={"Pages"},
     *     summary="Get pages data",
     *     security={{"bearer_token":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Get pages data successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Pages data retrieved successfully."),
     *             @OA\Property(property="data", type="object",
     *                  @OA\Property(property="current_page", type="integer", example=1),
     *                  @OA\Property(property="data", 
     *                      type="array", 
     *                      example={{ 
     *                          "id": 1,
     *                          "title": "Layanan",
     *                          "slug": "layanan",
     *                          "link": "/layanan",
     *                          "parent_id": null,
     *                          "created_at": "2024-09-12T06:33:25.000000Z",
     *                          "updated_at": "2024-09-12T06:33:25.000000Z"
     *                      }, {
     *                          "id": 2,
     *                          "title": "Penelitian",
     *                          "slug": "penelitian",
     *                          "link": "/layanan/penelitian",
     *                          "parent_id": 1,
     *                          "created_at": "2024-09-12T06:33:25.000000Z",
     *                          "updated_at": "2024-09-12T06:33:25.000000Z"
     *                      }},
     *                      @OA\Items(
     *                           type="object",
     *                           @OA\Property(property="id", type="integer", example=1),
     *                           @OA\Property(property="title", type="string", example="Layanan"),
     *                           @OA\Property(property="slug", type="string", example="layanan"),
     *                           @OA\Property(property="link", type="string", example="/layanan"),
     *                           @OA\Property(property="parent_id", type="string", example=null),
     *                           @OA\Property(property="created_at", type="string", format="date-time", example="2024-09-12T06:33:25.000000Z"),
     *                           @OA\Property(property="updated_at", type="string", format="date-time", example="2024-09-12T06:33:25.000000Z"),
     *                       ),
     *                       @OA\Items(
     *                           type="object",
     *                           @OA\Property(property="id", type="integer", example=2),
     *                           @OA\Property(property="title", type="string", example="Penelitian"),
     *                           @OA\Property(property="slug", type="string", example="penelitian"),
     *                           @OA\Property(property="link", type="string", example="/layanan/penelitian"),
     *                           @OA\Property(property="parent_id", type="string", example=1),
     *                           @OA\Property(property="created_at", type="string", format="date-time", example="2024-09-12T07:06:41.000000Z"),
     *                           @OA\Property(property="updated_at", type="string", format="date-time", example="2024-09-12T07:06:41.000000Z"),
     *                       )
     *                  ),
     *                  @OA\Property(property="first_page_url", type="string", example="http://localhost:8000/api/pages?page=1"),
     *                  @OA\Property(property="from", type="integer", example=1),
     *                  @OA\Property(property="last_page", type="integer", example=2),
     *                  @OA\Property(property="last_page_url", type="string", example="http://localhost:8000/api/pages?page=2"),
     *                  @OA\Property(property="links", 
     *                      type="array", 
     *                      example={{ 
     *                          "url": null,
     *                          "label": "&laquo; Previous",
     *                          "active": false
     *                      }, {
     *                          "url": "http://localhost:8000/api/pages?page=1",
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
     *                  @OA\Property(property="path", type="string", example="http://localhost:8000/api/pages"),
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
    public function getPages()
    {
        $authCheck = $this->checkIfSuperAdmin();
        if ($authCheck !== true) {
            return $authCheck;
        }

        $pages = Page::paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Page data retrieved successfully.',
            'data' => $pages
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/pages/menu",
     *     tags={"Pages"},
     *     summary="Get pages menu",
     *     @OA\Response(
     *         response=200,
     *         description="Get pages menu successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Pages menu retrieved successfully."),
     *             @OA\Property(property="data", type="object",
     *                  @OA\Property(property="data", 
     *                      type="array", 
     *                      example={{ 
     *                          "id": 1,
     *                          "title": "Layanan",
     *                          "slug": "layanan",
     *                          "link": "/layanan",
     *                          "parent_id": null,
     *                          "created_at": "2024-09-12T06:33:25.000000Z",
     *                          "updated_at": "2024-09-12T06:33:25.000000Z"
     *                      }, {
     *                          "id": 2,
     *                          "title": "Penelitian",
     *                          "slug": "penelitian",
     *                          "link": "/layanan/penelitian",
     *                          "parent_id": 1,
     *                          "created_at": "2024-09-12T06:33:25.000000Z",
     *                          "updated_at": "2024-09-12T06:33:25.000000Z"
     *                      }},
     *                      @OA\Items(
     *                           type="object",
     *                           @OA\Property(property="id", type="integer", example=1),
     *                           @OA\Property(property="title", type="string", example="Layanan"),
     *                           @OA\Property(property="slug", type="string", example="layanan"),
     *                           @OA\Property(property="link", type="string", example="/layanan"),
     *                           @OA\Property(property="parent_id", type="string", example=null),
     *                           @OA\Property(property="created_at", type="string", format="date-time", example="2024-09-12T06:33:25.000000Z"),
     *                           @OA\Property(property="updated_at", type="string", format="date-time", example="2024-09-12T06:33:25.000000Z"),
     *                       ),
     *                       @OA\Items(
     *                           type="object",
     *                           @OA\Property(property="id", type="integer", example=2),
     *                           @OA\Property(property="title", type="string", example="Penelitian"),
     *                           @OA\Property(property="slug", type="string", example="penelitian"),
     *                           @OA\Property(property="link", type="string", example="/layanan/penelitian"),
     *                           @OA\Property(property="parent_id", type="string", example=1),
     *                           @OA\Property(property="created_at", type="string", format="date-time", example="2024-09-12T07:06:41.000000Z"),
     *                           @OA\Property(property="updated_at", type="string", format="date-time", example="2024-09-12T07:06:41.000000Z"),
     *                       )
     *                  )
     *             )
     *         )
     *     ),
     * )
     */
    public function getPagesMenu()
    {
        $pages = Page::where('slug', '!=', 'beranda')->get();

        return response()->json([
            'success' => true,
            'message' => 'Pages menu retrieved successfully.',
            'data' => $pages
        ], 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/pages/{id}",
     *     tags={"Pages"},
     *     summary="Delete a page by ID",
     *     security={{"bearer_token":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the page",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Page data deleted successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Page data deleted successfully."),
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
     *         description="Page not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="string", example=false),
     *             @OA\Property(property="message", type="string", example="Page not found."),
     *         )
     *     )
     * )
     */
    public function delete($id)
    {
        $authCheck = $this->checkIfSuperAdmin();
        if ($authCheck !== true) {
            return $authCheck;
        }

        $page = Page::find($id);
        if (!$page) {
            return response()->json([
                'success' => false,
                'message' => 'Page not found.'
            ], 404);
        }

        $page->delete();

        return response()->json([
            'success' => true,
            'message' => 'Page deleted successfully.'
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
