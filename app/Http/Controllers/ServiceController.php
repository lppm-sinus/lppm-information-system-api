<?php

namespace App\Http\Controllers;

use App\Imports\ServiceImport;
use App\Models\Author;
use App\Models\Service;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;

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

            if ($request->boolean('reset_table')) {
                DB::table('author_service')->delete();
                DB::table('services')->delete();
            }

            Excel::import($import, $request->file('file'));

            return $this->successResponse(null, 'Services data imported successfully.', 201);
        } catch (ValidationException $e) {
            $failures = $e->failures();

            return $this->importValidationErrorsResponse($failures, 422);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_ketua' => 'required|string',
            'nidn_ketua' => 'required|string',
            'afiliasi_ketua' => 'required|string',
            'kd_pt_ketua' => 'required|string',
            'judul' => 'required|string',
            'nama_singkat_skema' => 'required|string',
            'thn_pertama_usulan' => 'required|string',
            'thn_usulan_kegiatan' => 'required|string',
            'thn_pelaksanaan_kegiatan' => 'required|string',
            'lama_kegiatan' => 'required|string',
            'bidang_fokus' => 'required|string',
            'nama_skema' => 'required|string',
            'status_usulan' => 'required|string',
            'dana_disetujui' => 'required|integer',
            'afiliasi_sinta_id' => 'required|string',
            'nama_institusi_penerima_dana' => 'required|string',
            'target_tkt' => 'required|string',
            'nama_program_hibah' => 'required|string',
            'kategori_sumber_dana' => 'required|string',
            'negara_sumber_dana' => 'required|string',
            'sumber_dana' => 'required|string',
            'author_members' => 'nullable|array',
            'author_members.*' => 'exists:authors,id',
        ]);

        if ($validator->fails()) {
            return $this->formatValidationErrors($validator);
        }

        $author = Author::where('nidn', $request->input('nidn_ketua'))->first();
        if (!$author) {
            return $this->errorResponse('Author not found.', 404);
        }

        $service = Service::create($request->all());
        $service->authors()->attach($author->id);
        $service->authors()->attach($request->author_members);
        $service->save();

        $service->load('authors');

        return $this->successResponse($service, 'Service created successfully.', 201);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nama_ketua' => 'required|string',
            'nidn_ketua' => 'required|string',
            'afiliasi_ketua' => 'required|string',
            'kd_pt_ketua' => 'required|string',
            'judul' => 'required|string',
            'nama_singkat_skema' => 'required|string',
            'thn_pertama_usulan' => 'required|string',
            'thn_usulan_kegiatan' => 'required|string',
            'thn_pelaksanaan_kegiatan' => 'required|string',
            'lama_kegiatan' => 'required|string',
            'bidang_fokus' => 'required|string',
            'nama_skema' => 'required|string',
            'status_usulan' => 'required|string',
            'dana_disetujui' => 'required|integer',
            'afiliasi_sinta_id' => 'required|string',
            'nama_institusi_penerima_dana' => 'required|string',
            'target_tkt' => 'required|string',
            'nama_program_hibah' => 'required|string',
            'kategori_sumber_dana' => 'required|string',
            'negara_sumber_dana' => 'required|string',
            'sumber_dana' => 'required|string',
            'author_members' => 'nullable|array',
            'author_members.*' => 'exists:authors,id',
        ]);

        if ($validator->fails()) {
            return $this->formatValidationErrors($validator);
        }

        $service = Service::find($id);
        if (!$service) {
            return $this->errorResponse('Service not found.', 404);
        }

        $author = Author::where('nidn', $request->input('nidn_ketua'))->first();
        if (!$author) {
            return $this->errorResponse('Author with NIDN ' . $author->nidn_ketua . ' not found.', 404);
        }

        $service->update($request->all());
        $service->authors()->sync([$author->id]);
        $service->authors()->syncWithoutDetaching($request->author_members);
        $service->save();

        $service->load('authors');

        return $this->successResponse($service, 'Service updated successfully.', 200);
    }

    public function getServices()
    {
        $query = Service::query();

        if (request()->has('q')) {
            $search_term = request()->input('q');
            $query->whereAny(['nama_ketua', 'nidn_ketua'], 'like', "%$search_term%");
        }

        $services = $query->with('authors')->paginate(10);

        return $this->paginatedResponse($services, 'Services retrieved successfully.', 200);
    }

    public function getServiceByID($id)
    {
        $service = Service::with('authors')->find($id);
        if (!$service) {
            return $this->errorResponse('Service not found.', 404);
        }

        return $this->successResponse($service, 'Service retrieved successfully.', 200);
    }

    public function getServicesGroupedByScheme()
    {
        $query = Service::with('authors');

        if (request()->has('study_program_id')) {
            $study_program_id = request()->input('study_program_id');
            $query->whereHas('authors', function ($q) use ($study_program_id) {
                $q->where('study_program_id', $study_program_id);
            });
        }

        $services = $query->get();
        $grouped_data = $services->groupBy('nama_singkat_skema')->map(function ($group) {
            return [
                'count' => $group->count(),
                'data' => $group
            ];
        });

        return $this->successResponse($grouped_data, 'Services retrieved successfully.', 200);
    }

    public function delete($id)
    {
        $service = Service::find($id);
        if (!$service) {
            return $this->errorResponse('Service not found.', 404);
        }

        $service->delete();

        return $this->successResponse(null, 'Service deleted successfully.', 200);
    }
}
