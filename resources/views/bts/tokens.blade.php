@extends('layouts.app')

@section('title', 'Token Management - BTS Tracker')

@section('content')
<div class="page-header">
    <h2>🔑 Token Management</h2>
    <p>Kelola API tokens untuk Unwired Labs</p>
</div>

<!-- Add Token Card -->
<div class="card" style="margin-bottom: 24px;">
    <h3 style="font-size: 16px; font-weight: 600; margin-bottom: 16px; color: #1e293b;">
        ➕ Tambah Token Baru
    </h3>
    
    <form id="addTokenForm">
        @csrf
        <div style="display: grid; grid-template-columns: 2fr 1fr auto; gap: 12px; align-items: end;">
            <div class="form-group" style="margin-bottom: 0;">
                <label for="token">API Token <span style="color: #ef4444;">*</span></label>
                <input type="text" id="token" name="token" placeholder="pk.xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" required>
                <div class="error-message" id="error-token"></div>
            </div>
            
            <div class="form-group" style="margin-bottom: 0;">
                <label for="notes">Notes (Optional)</label>
                <input type="text" id="notes" name="notes" placeholder="Catatan...">
            </div>
            
            <button type="submit" class="btn-primary" style="height: 44px;">
                <span id="addTokenBtnText">Tambah Token</span>
                <span id="addTokenSpinner" style="display:none;">⏳</span>
            </button>
        </div>
    </form>
</div>

<!-- Import CSV Card -->
<div class="card" style="margin-bottom: 24px; background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);">
    <h3 style="font-size: 16px; font-weight: 600; margin-bottom: 12px; color: #0c4a6e;">
        📁 Import Bulk Tokens (CSV)
    </h3>
    <p style="font-size: 13px; color: #64748b; margin-bottom: 16px;">
        Format CSV: <code style="background: #fff; padding: 2px 6px; border-radius: 4px; font-size: 12px;">token,notes</code> (baris pertama bisa header atau langsung data)
    </p>
    
    <form id="importCsvForm" enctype="multipart/form-data">
        @csrf
        <div style="display: flex; gap: 12px; align-items: center;">
            <input type="file" id="csv_file" name="csv_file" accept=".csv,.txt" required 
                   style="flex: 1; padding: 10px; border: 2px dashed #cbd5e1; border-radius: 8px; background: #fff; cursor: pointer;">
            <button type="submit" class="btn-primary">
                <span id="importBtnText">📤 Upload CSV</span>
                <span id="importSpinner" style="display:none;">⏳</span>
            </button>
        </div>
        <div id="importResult" style="margin-top: 12px; font-size: 13px;"></div>
    </form>
</div>

<!-- Tokens List -->
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3 style="font-size: 16px; font-weight: 600; color: #1e293b;">
            📋 Daftar Token (<span id="tokenCount">{{ $tokens->count() }}</span>)
        </h3>
        <div style="font-size: 13px; color: #64748b;">
            Total Usage: <strong id="totalUsage">{{ $tokens->sum('usage_count') }}</strong>
        </div>
    </div>

    <div id="tokensContainer">
        @if($tokens->count() > 0)
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                        <th style="padding: 12px; text-align: left; font-size: 13px; font-weight: 600; color: #475569;">Token</th>
                        <th style="padding: 12px; text-align: left; font-size: 13px; font-weight: 600; color: #475569;">Usage</th>
                        <th style="padding: 12px; text-align: left; font-size: 13px; font-weight: 600; color: #475569;">Status</th>
                        <th style="padding: 12px; text-align: left; font-size: 13px; font-weight: 600; color: #475569;">Notes</th>
                        <th style="padding: 12px; text-align: left; font-size: 13px; font-weight: 600; color: #475569;">Dibuat</th>
                        <th style="padding: 12px; text-align: center; font-size: 13px; font-weight: 600; color: #475569;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tokensList">
                    @foreach($tokens as $token)
                    <tr data-token-id="{{ $token->id }}" style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 12px; font-family: 'Courier New', monospace; font-size: 12px; color: #1e293b;">
                            {{ $token->token }}
                        </td>
                        <td style="padding: 12px;">
                            <span class="usage-badge" style="background: {{ $token->usage_count > 0 ? '#dbeafe' : '#f1f5f9' }}; color: {{ $token->usage_count > 0 ? '#1e40af' : '#64748b' }}; padding: 4px 12px; border-radius: 12px; font-size: 13px; font-weight: 600;">
                                {{ $token->usage_count }}
                            </span>
                        </td>
                        <td style="padding: 12px;">
                            @if($token->is_active)
                            <span style="background: #dcfce7; color: #166534; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">
                                ✓ Aktif
                            </span>
                            @else
                            <span style="background: #fee2e2; color: #991b1b; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">
                                ✗ Nonaktif
                            </span>
                            @endif
                        </td>
                        <td style="padding: 12px; font-size: 13px; color: #64748b;">
                            {{ $token->notes ?? '-' }}
                        </td>
                        <td style="padding: 12px; font-size: 12px; color: #94a3b8;">
                            {{ $token->created_at->diffForHumans() }}
                        </td>
                        <td style="padding: 12px; text-align: center;">
                            <div style="display: flex; gap: 6px; justify-content: center;">
                                <button onclick="resetUsage({{ $token->id }})" 
                                        class="btn-sm" 
                                        style="background: #fef3c7; color: #92400e; border: none; padding: 6px 10px; border-radius: 6px; font-size: 11px; cursor: pointer; font-weight: 600;"
                                        title="Reset Usage">
                                    🔄
                                </button>
                                <button onclick="toggleStatus({{ $token->id }}, {{ $token->is_active ? 'false' : 'true' }})" 
                                        class="btn-sm" 
                                        style="background: {{ $token->is_active ? '#fee2e2' : '#dcfce7' }}; color: {{ $token->is_active ? '#991b1b' : '#166534' }}; border: none; padding: 6px 10px; border-radius: 6px; font-size: 11px; cursor: pointer; font-weight: 600;"
                                        title="{{ $token->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                    {{ $token->is_active ? '⏸️' : '▶️' }}
                                </button>
                                <button onclick="deleteToken({{ $token->id }})" 
                                        class="btn-sm" 
                                        style="background: #fee2e2; color: #991b1b; border: none; padding: 6px 10px; border-radius: 6px; font-size: 11px; cursor: pointer; font-weight: 600;"
                                        title="Hapus">
                                    🗑️
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div style="text-align: center; padding: 60px 20px; color: #94a3b8;">
            <div style="font-size: 48px; margin-bottom: 16px;">🔑</div>
            <p style="font-size: 15px; font-weight: 500; color: #64748b;">Belum ada token yang ditambahkan</p>
            <p style="font-size: 13px; color: #94a3b8; margin-top: 8px;">Tambahkan token Unwired Labs API Anda untuk mulai tracking usage</p>
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    .btn-sm:hover {
        opacity: 0.8;
        transform: translateY(-1px);
        transition: all 0.2s;
    }

    tbody tr:hover {
        background: #f8fafc;
    }

    input[type="file"]::file-selector-button {
        background: #4F7CFF;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
        font-size: 13px;
    }

    input[type="file"]::file-selector-button:hover {
        background: #3d63e0;
    }
</style>
@endpush

@push('scripts')
<script>
    // Add Token
    $('#addTokenForm').on('submit', function(e) {
        e.preventDefault();
        
        $('#addTokenBtnText').hide();
        $('#addTokenSpinner').show();
        $('.error-message').text('').hide();
        
        $.ajax({
            url: '{{ route("tokens.store") }}',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    showNotification('✅ ' + response.message, 'success');
                    setTimeout(() => location.reload(), 1000);
                }
            },
            error: function(xhr) {
                $('#addTokenBtnText').show();
                $('#addTokenSpinner').hide();
                
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    if (errors.token) {
                        $('#error-token').text(errors.token[0]).show();
                    }
                    showNotification('❌ ' + xhr.responseJSON.message, 'error');
                } else {
                    showNotification('❌ Terjadi kesalahan', 'error');
                }
            }
        });
    });

    // Import CSV
    $('#importCsvForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        $('#importBtnText').hide();
        $('#importSpinner').show();
        $('#importResult').html('');
        
        $.ajax({
            url: '{{ route("tokens.import") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $('#importBtnText').show();
                $('#importSpinner').hide();
                
                if (response.success) {
                    let resultHtml = `<div style="background: #dcfce7; color: #166534; padding: 12px; border-radius: 8px; border-left: 4px solid #16a34a;">
                        <strong>✅ ${response.message}</strong>
                    `;
                    
                    if (response.errors && response.errors.length > 0) {
                        resultHtml += '<ul style="margin-top: 8px; padding-left: 20px; font-size: 12px;">';
                        response.errors.forEach(error => {
                            resultHtml += `<li>${error}</li>`;
                        });
                        resultHtml += '</ul>';
                    }
                    
                    resultHtml += '</div>';
                    $('#importResult').html(resultHtml);
                    
                    showNotification('✅ Import berhasil!', 'success');
                    setTimeout(() => location.reload(), 2000);
                }
            },
            error: function(xhr) {
                $('#importBtnText').show();
                $('#importSpinner').hide();
                
                const message = xhr.responseJSON?.message || 'Terjadi kesalahan saat import';
                $('#importResult').html(`
                    <div style="background: #fee2e2; color: #991b1b; padding: 12px; border-radius: 8px; border-left: 4px solid #dc2626;">
                        <strong>❌ ${message}</strong>
                    </div>
                `);
                showNotification('❌ ' + message, 'error');
            }
        });
    });

    // Reset Usage
    function resetUsage(id) {
        if (!confirm('Reset usage count untuk token ini?')) return;
        
        $.ajax({
            url: `/api/tokens/${id}/reset-usage`,
            method: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            success: function(response) {
                if (response.success) {
                    showNotification('✅ Usage count berhasil direset', 'success');
                    setTimeout(() => location.reload(), 800);
                }
            },
            error: function() {
                showNotification('❌ Gagal reset usage count', 'error');
            }
        });
    }

   // Toggle Status
function toggleStatus(id, isActive) {
    const statusText = isActive === 'true' ? 'aktifkan' : 'nonaktifkan';
    
    if (!confirm(`Yakin ingin ${statusText} token ini?`)) return;
    
    $.ajax({
        url: `/api/tokens/${id}`,
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        contentType: 'application/json',
        data: JSON.stringify({
            is_active: isActive === 'true' ? 1 : 0
        }),
        success: function(response) {
            if (response.success) {
                const status = isActive === 'true' ? 'diaktifkan' : 'dinonaktifkan';
                showNotification(`✅ Token berhasil ${status}`, 'success');
                setTimeout(() => location.reload(), 800);
            }
        },
        error: function(xhr) {
            console.error('Toggle error:', xhr);
            const message = xhr.responseJSON?.message || 'Gagal mengubah status token';
            showNotification('❌ ' + message, 'error');
        }
    });
}

    // Delete Token
    function deleteToken(id) {
        if (!confirm('Yakin ingin menghapus token ini?')) return;
        
        $.ajax({
            url: `/api/tokens/${id}`,
            method: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
            success: function(response) {
                if (response.success) {
                    showNotification('✅ Token berhasil dihapus', 'success');
                    $(`tr[data-token-id="${id}"]`).fadeOut(400, function() {
                        $(this).remove();
                        updateCounts();
                    });
                }
            },
            error: function() {
                showNotification('❌ Gagal menghapus token', 'error');
            }
        });
    }

    // Update counts
    function updateCounts() {
        const count = $('#tokensList tr').length;
        let totalUsage = 0;
        
        $('#tokensList .usage-badge').each(function() {
            totalUsage += parseInt($(this).text()) || 0;
        });
        
        $('#tokenCount').text(count);
        $('#totalUsage').text(totalUsage);
        
        if (count === 0) {
            $('#tokensContainer').html(`
                <div style="text-align: center; padding: 60px 20px; color: #94a3b8;">
                    <div style="font-size: 48px; margin-bottom: 16px;">🔑</div>
                    <p style="font-size: 15px; font-weight: 500; color: #64748b;">Belum ada token yang ditambahkan</p>
                    <p style="font-size: 13px; color: #94a3b8; margin-top: 8px;">Tambahkan token Unwired Labs API Anda untuk mulai tracking usage</p>
                </div>
            `);
        }
    }

    // Notification function
    function showNotification(message, type) {
        const bgColor = type === 'success' ? '#dcfce7' : '#fee2e2';
        const textColor = type === 'success' ? '#166534' : '#991b1b';
        const borderColor = type === 'success' ? '#16a34a' : '#dc2626';
        
        const notification = $(`
            <div style="position: fixed; top: 24px; right: 24px; z-index: 9999; 
                        background: ${bgColor}; color: ${textColor}; 
                        padding: 16px 24px; border-radius: 12px; 
                        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
                        border-left: 4px solid ${borderColor};
                        font-weight: 600; font-size: 14px;
                        animation: slideInRight 0.3s ease-out;">
                ${message}
            </div>
        `);
        
        $('body').append(notification);
        
        setTimeout(() => {
            notification.fadeOut(400, function() {
                $(this).remove();
            });
        }, 3000);
    }
</script>

<style>
    @keyframes slideInRight {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
</style>
@endpush