<?php

namespace App\Http\Controllers;

use App\Models\Search;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class BtsController extends Controller
{
    /**
     * Display Cell ID Lookup page
     */
    public function index()
    {
        return view('bts.index');
    }

    /**
     * Search BTS location via API
     */
    public function search(Request $request)
{
    try {
        $request->validate([
            'radio_type' => 'required|in:lte,gsm,umts,cdma,nr',
            'mcc' => 'required|string',
            'mnc' => 'required|string',
            'lac' => 'required|string',
            'cid' => 'required|string',
            'token_id' => 'nullable|exists:tokens,id',
        ]);

        // Get token TANPA increment dulu
        $tokenModel = null;
        $tokenUsed = null;
        $tokenSource = 'default (.env)';
        
        if ($request->token_id) {
            $tokenModel = \App\Models\Token::findOrFail($request->token_id);
            $apiKey = $tokenModel->token;
            $tokenUsed = [
                'id' => $tokenModel->id,
                'token' => substr($tokenModel->token, 0, 20) . '...',
                'usage_count_before' => $tokenModel->usage_count,
            ];
            $tokenSource = 'database (ID: ' . $tokenModel->id . ')';
            
            \Log::info('Token akan digunakan:', [
                'token_id' => $tokenModel->id,
                'usage_before' => $tokenModel->usage_count
            ]);
        } else {
            $apiKey = env('UNWIREDLABS_API_KEY', 'test');
            \Log::info('Menggunakan default token dari .env');
        }

        \Log::info('API Key digunakan: ' . substr($apiKey, 0, 10) . '...');

        $requestData = [
            'token' => $apiKey,
            'radio' => $request->radio_type,
            'mcc' => (int) $request->mcc,
            'mnc' => (int) $request->mnc,
            'cells' => [[
                'lac' => (int) $request->lac,
                'cid' => (int) $request->cid,
            ]],
            'address' => 1
        ];

        // API CALL
        $response = Http::timeout(30)->post('https://us1.unwiredlabs.com/v2/process.php', $requestData);
        
        if (!$response->successful()) {
            throw new \Exception('API request failed with status ' . $response->status());
        }

        $data = $response->json();

        // ✅ API BERHASIL DIPANGGIL - INCREMENT TOKEN SEKARANG
        if ($tokenModel) {
            $tokenModel->incrementUsage();
            $tokenUsed['usage_count_after'] = $tokenModel->usage_count;
            
            \Log::info('Token berhasil digunakan:', [
                'token_id' => $tokenModel->id,
                'usage_after' => $tokenModel->usage_count,
                'api_status' => $data['status'] ?? 'unknown'
            ]);
        }

        // Save to database
        $search = Search::create([
            'radio_type' => $request->radio_type,
            'mcc' => $request->mcc,
            'mnc' => $request->mnc,
            'lac' => $request->lac,
            'cid' => $request->cid,
            'latitude' => $data['lat'] ?? null,
            'longitude' => $data['lon'] ?? null,
            'accuracy' => $data['accuracy'] ?? null,
            'address' => $data['address'] ?? null,
            'status' => $data['status'] ?? 'error',
            'error_message' => $data['message'] ?? null,
            'raw_response' => $data,
            'ip_address' => $request->ip(),
        ]);

        // Response sukses atau gagal, token tetap terpakai karena API sudah dipanggil
        if (isset($data['status']) && $data['status'] === 'ok') {
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $search->id,
                    'lat' => $data['lat'],
                    'lon' => $data['lon'],
                    'accuracy' => $data['accuracy'],
                    'address' => $data['address'] ?? 'Alamat tidak tersedia',
                    'balance' => $data['balance'] ?? null,
                ],
                'token_info' => $tokenUsed,
                'token_source' => $tokenSource,
                'message' => 'Lokasi BTS berhasil ditemukan!'
            ]);
        }

        // API response error (cell not found, etc) - token tetap terpakai
        return response()->json([
            'success' => false,
            'data' => null,
            'token_info' => $tokenUsed,
            'token_source' => $tokenSource,
            'message' => $data['message'] ?? 'Lokasi BTS tidak ditemukan. Pastikan Cell ID yang Anda masukkan benar.'
        ], 400);

    } catch (\Illuminate\Validation\ValidationException $e) {
        // ❌ VALIDATION ERROR - TOKEN TIDAK TERPAKAI
        \Log::warning('Validation error - Token tidak terpakai');
        
        return response()->json([
            'success' => false,
            'message' => 'Data yang Anda masukkan tidak valid.',
            'errors' => $e->errors(),
            'token_used' => false
        ], 422);
        
    } catch (\Exception $e) {
        // ❌ NETWORK ERROR / TIMEOUT - TOKEN TIDAK TERPAKAI
        \Log::error('BTS Search Error (Token tidak terpakai): ' . $e->getMessage());

        Search::create([
            'radio_type' => $request->radio_type ?? 'unknown',
            'mcc' => $request->mcc ?? 0,
            'mnc' => $request->mnc ?? 0,
            'lac' => $request->lac ?? 0,
            'cid' => $request->cid ?? 0,
            'status' => 'error',
            'error_message' => $e->getMessage(),
            'ip_address' => $request->ip(),
        ]);

        $errorMessage = 'Terjadi kesalahan saat mencari lokasi BTS.';
        
        if (strpos($e->getMessage(), 'timeout') !== false) {
            $errorMessage = 'Request timeout. Koneksi ke server Unwired Labs terlalu lama.';
        } elseif (strpos($e->getMessage(), 'Connection') !== false) {
            $errorMessage = 'Tidak dapat terhubung ke server Unwired Labs. Periksa koneksi internet Anda.';
        }

        return response()->json([
            'success' => false,
            'message' => $errorMessage,
            'token_used' => false
        ], 500);
    }
}

    /**
     * Display triangulation page
     */
    public function triangulation()
    {
        return view('bts.triangulation');
    }

    /**
     * Display search history
     */
    public function history()
    {
        $searches = Search::orderBy('created_at', 'desc')->paginate(50);
        return view('bts.history', compact('searches'));
    }

    /**
     * Display analytics page
     */
    public function analytics()
    {
        $stats = Search::getStats();
        $operatorStats = Search::selectRaw('mnc, COUNT(*) as count')
            ->groupBy('mnc')
            ->orderByDesc('count')
            ->get();
        
        $uniqueCells = Search::selectRaw('CONCAT(mcc, "-", mnc, "-", lac, "-", cid) as cell_id')
            ->distinct()
            ->count();

        $recentSearches = Search::orderBy('created_at', 'desc')->limit(10)->get();

        return view('bts.analytics', compact('stats', 'operatorStats', 'uniqueCells', 'recentSearches'));
    }

    /**
     * Delete a search record
     */
    public function destroy($id)
    {
        Search::findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Record berhasil dihapus']);
    }

    /**
     * Clear all search history
     */
    public function clearHistory()
    {
        Search::truncate();
        return response()->json(['success' => true, 'message' => 'Semua history berhasil dihapus']);
    }

    public function sectorCalculator()
    {
        return view('bts.sector-calculator');
    }
}