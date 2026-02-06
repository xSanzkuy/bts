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
                'mcc' => 'required|integer|min:1|max:999',
                'mnc' => 'required|integer|min:0|max:999',
                'lac' => 'required|integer|min:0',
                'cid' => 'required|integer|min:0',
            ]);

            $apiKey = env('UNWIREDLABS_API_KEY', 'test');

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

            $response = Http::timeout(30)->post('https://us1.unwiredlabs.com/v2/process.php', $requestData);
            
            if (!$response->successful()) {
                throw new \Exception('API request failed with status ' . $response->status());
            }

            $data = $response->json();

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

            if (isset($data['status']) && $data['status'] === 'ok') {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'id' => $search->id,
                        'lat' => $data['lat'],
                        'lon' => $data['lon'],
                        'accuracy' => $data['accuracy'],
                        'address' => $data['address'] ?? 'Alamat tidak tersedia',
                    ],
                    'message' => 'Lokasi BTS berhasil ditemukan!'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $data['message'] ?? 'Lokasi BTS tidak ditemukan. Pastikan Cell ID yang Anda masukkan benar.'
            ], 400);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data yang Anda masukkan tidak valid.',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            \Log::error('BTS Search Error: ' . $e->getMessage());

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
                'message' => $errorMessage
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

