/**
 * Campus Helpdesk – Global App JS
 * ============================================================
 * Provides utility functions used across all views.
 */

'use strict';

// ---- Flash message ----
/**
 * Show a dismissible Bootstrap alert in #flash-area
 * @param {'success'|'danger'|'warning'|'info'} type
 * @param {string} message  HTML string
 * @param {number} duration Auto-dismiss after ms (default 4000)
 */
function showFlash(type, message, duration = 4000) {
    const area = document.getElementById('flash-area');
    if (!area) return;
    const id  = 'flash-' + Date.now();
    const map = {
        success: '<i class="bi bi-check-circle-fill me-2"></i>',
        danger:  '<i class="bi bi-exclamation-octagon-fill me-2"></i>',
        warning: '<i class="bi bi-exclamation-triangle-fill me-2"></i>',
        info:    '<i class="bi bi-info-circle-fill me-2"></i>',
    };
    const icon = map[type] ?? '';
    const html = `
        <div id="${id}" class="alert alert-${type} alert-dismissible fade show py-2 mb-2" role="alert">
            ${icon}${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>`;
    area.insertAdjacentHTML('beforeend', html);
    if (duration > 0) {
        setTimeout(() => {
            const el = document.getElementById(id);
            if (el) bootstrap.Alert.getOrCreateInstance(el).close();
        }, duration);
    }
}

// ---- Generic fetch helper ----
/**
 * POST form data via fetch and return parsed JSON.
 * @param {string} url
 * @param {FormData|URLSearchParams|object} data
 */
async function postJson(url, data) {
    let body = data;
    const headers = { 'X-Requested-With': 'XMLHttpRequest' };

    if (data instanceof FormData) {
        // Let browser set multipart boundary
    } else if (typeof data === 'object' && !(data instanceof URLSearchParams)) {
        body = new URLSearchParams(data);
        headers['Content-Type'] = 'application/x-www-form-urlencoded';
    }

    const resp = await fetch(url, { method: 'POST', headers, body });
    if (!resp.ok && resp.status !== 422) {
        throw new Error(`HTTP ${resp.status}`);
    }
    return resp.json();
}

// ---- Confirm-delete helper ----
/**
 * Attach click handler to elements matching selector.
 * @param {string} selector  CSS selector
 * @param {string} url       DELETE endpoint
 * @param {function} onSuccess  Called with response if successful
 */
function bindDeleteButtons(selector, url, onSuccess) {
    document.querySelectorAll(selector).forEach(btn => {
        btn.addEventListener('click', async function () {
            const confirmMsg = this.dataset.confirm ?? 'Xác nhận xoá?';
            if (!confirm(confirmMsg)) return;
            try {
                const res = await postJson(url, { id: this.dataset.id });
                if (res.success) {
                    showFlash('success', res.message ?? 'Đã xoá thành công');
                    if (typeof onSuccess === 'function') onSuccess(res, this);
                } else {
                    showFlash('danger', res.message ?? 'Có lỗi xảy ra');
                }
            } catch (e) {
                showFlash('danger', 'Lỗi kết nối: ' + e.message);
            }
        });
    });
}

// ---- Tooltip init (Bootstrap) ----
document.addEventListener('DOMContentLoaded', () => {
    // Init all Bootstrap tooltips
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
        new bootstrap.Tooltip(el);
    });

    // Auto-close existing alerts after 5s
    document.querySelectorAll('.alert:not(.alert-permanent)').forEach(el => {
        setTimeout(() => {
            if (el && el.isConnected) bootstrap.Alert.getOrCreateInstance(el).close();
        }, 5000);
    });
});

// ---- Number formatter ----
function formatNumber(n) {
    return new Intl.NumberFormat('vi-VN').format(n);
}

// ---- Date helpers ----
function timeAgo(dateStr) {
    const now = Date.now();
    const then = new Date(dateStr).getTime();
    const diff = Math.floor((now - then) / 1000);
    if (diff < 60) return 'vừa xong';
    if (diff < 3600) return Math.floor(diff / 60) + ' phút trước';
    if (diff < 86400) return Math.floor(diff / 3600) + ' giờ trước';
    return Math.floor(diff / 86400) + ' ngày trước';
}
