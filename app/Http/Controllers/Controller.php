<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

/**
 * @OA\Info(
 *   version="1.0.0",
 *   title="LPPM API",
 *   @OA\License(name="SINUS"),
 *   @OA\Attachable()
 * )
 */

abstract class Controller
{
    public function checkIfSuperAdmin()
    {
        $currentUser = Auth::user();
        if (!$currentUser || $currentUser->role !== 'superadmin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.'
            ], 403);
        }

        return true;
    }
}
