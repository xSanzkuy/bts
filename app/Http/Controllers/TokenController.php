<?php

namespace App\Http\Controllers;

use App\Models\Token;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TokenController extends Controller
{
    public function index()
    {
        $tokens = Token::orderBy('created_at', 'desc')->get();
        return view('bts.tokens', compact('tokens'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string|unique:tokens,token',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Token sudah ada atau tidak valid',
                'errors' => $validator->errors()
            ], 422);
        }

        $token = Token::create([
            'token' => $request->token,
            'notes' => $request->notes,
            'usage_count' => 0,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Token berhasil ditambahkan',
            'data' => $token
        ]);
    }

    public function importCsv(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'File tidak valid. Harus berupa CSV/TXT maksimal 2MB',
            ], 422);
        }

        try {
            $file = $request->file('csv_file');
            $csvData = array_map('str_getcsv', file($file->getRealPath()));
            
            $imported = 0;
            $skipped = 0;
            $errors = [];

            foreach ($csvData as $index => $row) {
                if ($index === 0 && (strtolower($row[0]) === 'token' || strtolower($row[0]) === 'api_key')) {
                    continue;
                }

                $tokenValue = trim($row[0] ?? '');
                $notes = trim($row[1] ?? '');

                if (empty($tokenValue)) {
                    $skipped++;
                    continue;
                }

               if (Token::where('token', $tokenValue)->exists()) {
                    $skipped++;
                    $errors[] = "Baris " . ($index + 1) . ": Token sudah ada - $tokenValue";
                    continue;
                }

                Token::create([
                    'token' => $tokenValue,
                    'notes' => $notes,
                    'usage_count' => 0,
                    'is_active' => true,
                ]);

                $imported++;
            }

            return response()->json([
                'success' => true,
                'message' => "Berhasil import $imported token" . ($skipped > 0 ? ", $skipped dilewati" : ""),
                'imported' => $imported,
                'skipped' => $skipped,
                'errors' => $errors,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal import CSV: ' . $e->getMessage(),
            ], 500);
        }
    }

   public function update(Request $request, $id)
{
    try {
        $token = Token::findOrFail($id);

        // Ambil data dari request
        $data = $request->all();
        
        // Log untuk debug
        \Log::info('Token update request:', [
            'id' => $id,
            'data' => $data
        ]);

        // Validasi
        $rules = [
            'is_active' => 'sometimes|boolean',
            'token' => 'sometimes|string|unique:tokens,token,' . $id,
            'notes' => 'nullable|string|max:500',
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid',
                'errors' => $validator->errors()
            ], 422);
        }

        // Update hanya field yang dikirim
        if (isset($data['is_active'])) {
            $token->is_active = (bool) $data['is_active'];
        }
        
        if (isset($data['token'])) {
            $token->token = $data['token'];
        }
        
        if (isset($data['notes'])) {
            $token->notes = $data['notes'];
        }

        $token->save();

        \Log::info('Token updated successfully:', [
            'id' => $token->id,
            'is_active' => $token->is_active
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Token berhasil diupdate',
            'data' => $token
        ]);

    } catch (\Exception $e) {
        \Log::error('Token update error:', [
            'id' => $id,
            'error' => $e->getMessage()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Gagal mengubah status token: ' . $e->getMessage()
        ], 500);
    }
}
    public function destroy($id)
    {
        try {
            $token = Token::findOrFail($id);
            $token->delete();

            return response()->json([
                'success' => true,
                'message' => 'Token berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus token'
            ], 500);
        }
    }

    public function resetUsage($id)
    {
        try {
            $token = Token::findOrFail($id);
            $token->update(['usage_count' => 0]);

            return response()->json([
                'success' => true,
                'message' => 'Usage count berhasil direset',
                'data' => $token
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal reset usage count'
            ], 500);
        }
    }

    public function getActiveTokens()
    {
        $tokens = Token::active()->orderBy('created_at', 'desc')->get();
        
        return response()->json([
            'success' => true,
            'data' => $tokens
        ]);
    }
}