<?php

namespace App\Http\Controllers;

use App\Models\Url;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ShortenController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'long_url' => 'required|url|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ]);
        }

        $urlObj = new Url();
        $hash = $urlObj->generateHash($request->long_url);

        DB::beginTransaction();

        try {
            $existingUrl = $urlObj->getExistingUrl($hash, auth()->id(), true);

            if ($existingUrl) {
                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => 'Success',
                    'data' => $existingUrl
                ]);
            }

            $url = Url::create([
                'long_url' => $request->long_url,
                'code' => null,
                'hash' => $hash,
                'user_id' => auth()->id(),
            ]);

            $code = $urlObj->generateCodee($url->id);
            $url->update(['code' => $code]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Success',
                'data' => $url
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('URL_STORE_ERROR', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong'
            ]);
        }
    }
}
