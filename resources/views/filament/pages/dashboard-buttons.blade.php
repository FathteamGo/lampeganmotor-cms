<div class="hana-ai-card"
     x-data="{
         loadingReport: false,
         loadingInsight: false,

         async sendReport() {
             if (this.loadingReport) return;
             if (!confirm('Generate & Kirim Laporan Mingguan?\nHana AI akan menyusun laporan mingguan dan mengirimkannya ke WhatsApp Anda.')) return;
             this.loadingReport = true;

             try {
                 await $wire.runWeeklyReport();
             } catch (e) {
                 alert('❌ Gagal: ' + (e.message || 'Tidak dapat menghubungi server. Coba lagi nanti.'));
             } finally {
                 this.loadingReport = false;
             }
         },

         async sendInsight() {
             if (this.loadingInsight) return;
             if (!confirm('Generate & Kirim Insight 30 Hari?\nHana AI akan merangkum insight bisnis strategis 30 hari ke belakang dan mengirimkannya ke WhatsApp Anda.')) return;
             this.loadingInsight = true;

             try {
                 await $wire.run30DayInsight();
             } catch (e) {
                 alert('❌ Gagal: ' + (e.message || 'Tidak dapat menghubungi server. Coba lagi nanti.'));
             } finally {
                 this.loadingInsight = false;
             }
         },
     }">

    <div class="hana-ai-card-header">
        <div class="hana-ai-avatar">
            🌸
        </div>
        <div class="hana-ai-header-text">
            <h3 class="hana-ai-title">Asisten Bisnis Hana AI</h3>
            <p class="hana-ai-subtitle">Analisis performa penjualan dan susun laporan strategis otomatis</p>
        </div>
    </div>

    <div class="hana-ai-actions-row">
        {{-- Tombol 1: Run Report AI Agent --}}
        <button
            type="button"
            @click="sendReport()"
            :disabled="loadingReport"
            class="hana-ai-btn hana-ai-btn-primary"
        >
            <span class="hana-ai-btn-content">
                <template x-if="!loadingReport">
                    <svg class="hana-ai-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m3.75 13.5 10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z" />
                    </svg>
                </template>
                <template x-if="loadingReport">
                    <svg class="hana-ai-icon animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </template>
                <span x-text="loadingReport ? 'Menyusun Laporan...' : 'Kirim Laporan Mingguan'"></span>
            </span>
        </button>

        {{-- Tombol 2: Insight 30 Hari --}}
        <button
            type="button"
            @click="sendInsight()"
            :disabled="loadingInsight"
            class="hana-ai-btn hana-ai-btn-secondary"
        >
            <span class="hana-ai-btn-content">
                <template x-if="!loadingInsight">
                    <svg class="hana-ai-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 0 0 1.5-.189m-1.5.189a6.01 6.01 0 0 1-1.5-.189m3.75 7.478a12.06 12.06 0 0 1-4.5 0m3.75 2.383a14.406 14.406 0 0 1-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 1 0-7.517 0c.85.493 1.509 1.333 1.509 2.316V18" />
                    </svg>
                </template>
                <template x-if="loadingInsight">
                    <svg class="hana-ai-icon animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </template>
                <span x-text="loadingInsight ? 'Merangkum Insight...' : 'Kirim Insight 30 Hari'"></span>
            </span>
        </button>
    </div>
</div>

<style>
    .hana-ai-card {
        background: linear-gradient(135deg, rgba(28, 28, 30, 0.75) 0%, rgba(18, 18, 18, 0.85) 100%);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 16px;
        padding: 24px;
        margin-bottom: 24px;
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .hana-ai-card-header {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .hana-ai-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: linear-gradient(135deg, rgba(244, 63, 94, 0.25) 0%, rgba(244, 63, 94, 0.08) 100%);
        border: 1px solid rgba(244, 63, 94, 0.35);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        box-shadow: 0 0 15px rgba(244, 63, 94, 0.2);
        animation: pulse-avatar 3s infinite alternate;
    }

    @keyframes pulse-avatar {
        0% { transform: scale(1); box-shadow: 0 0 10px rgba(244, 63, 94, 0.2); }
        100% { transform: scale(1.05); box-shadow: 0 0 20px rgba(244, 63, 94, 0.4); }
    }

    .hana-ai-header-text {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .hana-ai-title {
        font-size: 18px;
        font-weight: 700;
        color: #f3f4f6;
        margin: 0;
        letter-spacing: -0.025em;
    }

    .hana-ai-subtitle {
        font-size: 13px;
        color: #9ca3af;
        margin: 0;
    }

    .hana-ai-actions-row {
        display: flex;
        flex-wrap: wrap;
        gap: 14px;
    }

    .hana-ai-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        padding: 12px 24px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid rgba(255, 255, 255, 0.08);
        position: relative;
        overflow: hidden;
        min-width: 220px;
    }

    .hana-ai-btn-content {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        position: relative;
        z-index: 2;
    }

    .hana-ai-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(
            120deg,
            transparent,
            rgba(255, 255, 255, 0.15),
            transparent
        );
        transition: all 0.6s ease;
        z-index: 1;
    }

    .hana-ai-btn:hover::before {
        left: 100%;
    }

    .hana-ai-btn-primary {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: #ffffff;
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.25);
    }

    .hana-ai-btn-primary:hover:not(:disabled) {
        transform: translateY(-2px);
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        box-shadow: 0 8px 25px rgba(16, 185, 129, 0.45), 0 0 10px rgba(16, 185, 129, 0.2);
    }

    .hana-ai-btn-secondary {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: #ffffff;
        box-shadow: 0 4px 15px rgba(245, 158, 11, 0.25);
    }

    .hana-ai-btn-secondary:hover:not(:disabled) {
        transform: translateY(-2px);
        background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
        box-shadow: 0 8px 25px rgba(245, 158, 11, 0.45), 0 0 10px rgba(245, 158, 11, 0.2);
    }

    .hana-ai-btn:active:not(:disabled) {
        transform: translateY(1px);
    }

    .hana-ai-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none !important;
        box-shadow: none !important;
    }

    .hana-ai-icon {
        width: 18px;
        height: 18px;
        flex-shrink: 0;
        stroke: currentColor;
    }

    .animate-spin {
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
</style>
