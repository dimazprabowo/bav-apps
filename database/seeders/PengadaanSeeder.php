<?php

namespace Database\Seeders;

use App\Enums\PaymentApprovalStatus;
use App\Enums\PengadaanApprovalStatus;
use App\Enums\VendorStatus;
use App\Models\Cabang;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Pengadaan;
use App\Models\PengadaanEvidence;
use App\Models\PengadaanItem;
use App\Models\User;
use App\Models\Vendor;
use App\Services\FileStorageService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PengadaanSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@app.com')->first();
        if (! $admin) {
            return;
        }

        $fileStorage = app(FileStorageService::class);
        $disk = file_disk();

        $vendors = Vendor::where('status', VendorStatus::Aktif->value)->get()->keyBy('code');
        $priok = Cabang::where('code', 'TGP')->first();
        $surabaya = Cabang::where('code', 'SBY')->first();
        $makassar = Cabang::where('code', 'MKS')->first();
        $belawan = Cabang::where('code', 'BLW')->first();
        $batam = Cabang::where('code', 'BTM')->first();

        // 12 skenario pengadaan yang mencakup semua state:
        // approval (pending/approved/rejected) x invoice (none/partial/full) x payment (none/partial/lunas/pending/rejected)
        $scenarios = [
            // 1. Pending - menunggu approval, ada evidence completed
            [
                'no' => 'PGD-2025-001',
                'vendor_code' => 'VDR-001',
                'cabang' => $priok,
                'tanggal' => now()->subDays(2),
                'status' => PengadaanApprovalStatus::Pending,
                'items' => [
                    ['nama_item' => 'Multimeter Digital Fluke 87V', 'kategori' => 'Alat Ukur Listrik', 'qty' => 2, 'satuan' => 'unit', 'harga' => 8500000],
                    ['nama_item' => 'Kabel Test Set Premium', 'kategori' => 'Aksesoris', 'qty' => 5, 'satuan' => 'set', 'harga' => 1250000],
                ],
                'evidences' => [
                    ['name' => 'Surat Penawaran PT Sumber Alat Teknik.pdf'],
                    ['name' => 'Spesifikasi Teknis Multimeter.pdf'],
                ],
                'invoices' => [],
                'catatan' => 'Pengadaan multimeter untuk lab kalibrasi Priok.',
            ],
            // 2. Approved - belum ditagih (no invoice)
            [
                'no' => 'PGD-2025-002',
                'vendor_code' => 'VDR-002',
                'cabang' => $surabaya,
                'tanggal' => now()->subDays(15),
                'status' => PengadaanApprovalStatus::Approved,
                'approved_at' => now()->subDays(13),
                'items' => [
                    ['nama_item' => 'Caliper Digital Mitutoyo 500-196-30', 'kategori' => 'Alat Ukur Dimensi', 'qty' => 3, 'satuan' => 'unit', 'harga' => 4200000],
                ],
                'evidences' => [
                    ['name' => 'Quotation CV Kalibrasi Mandiri.pdf'],
                ],
                'invoices' => [],
                'catatan' => 'Pengadaan caliper untuk lab Surabaya.',
            ],
            // 3. Approved + invoice partial (ditagih sebagian) + payment approved (sebagian)
            [
                'no' => 'PGD-2025-003',
                'vendor_code' => 'VDR-003',
                'cabang' => $priok,
                'tanggal' => now()->subDays(40),
                'status' => PengadaanApprovalStatus::Approved,
                'approved_at' => now()->subDays(38),
                'items' => [
                    ['nama_item' => 'Marine GPS Chartplotter Garmin', 'kategori' => 'Marine Electronics', 'qty' => 1, 'satuan' => 'unit', 'harga' => 25000000],
                    ['nama_item' => 'VHF Marine Radio Standard Horizon', 'kategori' => 'Marine Electronics', 'qty' => 2, 'satuan' => 'unit', 'harga' => 3500000],
                ],
                'evidences' => [
                    ['name' => 'Penawaran Marine Equipment.pdf'],
                ],
                'invoices' => [
                    [
                        'no' => 'INV-MEI-2025-001',
                        'tanggal' => now()->subDays(35),
                        'jumlah' => 15000000,
                        'jatuh_tempo' => now()->subDays(5),
                        'catatan' => 'Invoice tahap 1 (DP 50%).',
                        'payments' => [
                            [
                                'tanggal_bayar' => now()->subDays(30),
                                'jumlah_bayar' => 10000000,
                                'metode' => 'Transfer Bank BCA',
                                'status' => PaymentApprovalStatus::Approved,
                                'approved_at' => now()->subDays(28),
                            ],
                        ],
                    ],
                ],
                'catatan' => 'Pengadaan alat navigasi kapal.',
            ],
            // 4. Approved + invoice full + payment approved (LUNAS)
            [
                'no' => 'PGD-2025-004',
                'vendor_code' => 'VDR-004',
                'cabang' => $surabaya,
                'tanggal' => now()->subDays(60),
                'status' => PengadaanApprovalStatus::Approved,
                'approved_at' => now()->subDays(58),
                'items' => [
                    ['nama_item' => 'Thermohygrometer Testo 608-H1', 'kategori' => 'Alat Ukur Lingkungan', 'qty' => 4, 'satuan' => 'unit', 'harga' => 3200000],
                ],
                'evidences' => [
                    ['name' => 'PO Thermohygrometer.pdf'],
                    ['name' => 'Sertifikat Kalibrasi Testo.pdf'],
                ],
                'invoices' => [
                    [
                        'no' => 'INV-FLK-2025-004',
                        'tanggal' => now()->subDays(55),
                        'jumlah' => 12800000,
                        'jatuh_tempo' => now()->subDays(25),
                        'catatan' => 'Invoice full.',
                        'payments' => [
                            [
                                'tanggal_bayar' => now()->subDays(30),
                                'jumlah_bayar' => 12800000,
                                'metode' => 'Transfer Bank Mandiri',
                                'status' => PaymentApprovalStatus::Approved,
                                'approved_at' => now()->subDays(28),
                            ],
                        ],
                    ],
                ],
                'catatan' => 'Pengadaan thermohygrometer untuk 4 cabang.',
            ],
            // 5. Approved + invoice full + payment pending (belum dibayar, butuh approval)
            [
                'no' => 'PGD-2025-005',
                'vendor_code' => 'VDR-005',
                'cabang' => $makassar,
                'tanggal' => now()->subDays(20),
                'status' => PengadaanApprovalStatus::Approved,
                'approved_at' => now()->subDays(18),
                'items' => [
                    ['nama_item' => 'Bor Listrik Bosch GSB 13 RE', 'kategori' => 'Power Tools', 'qty' => 6, 'satuan' => 'unit', 'harga' => 1850000],
                    ['nama_item' => 'Gerinda Tangan Makita 9555H', 'kategori' => 'Power Tools', 'qty' => 4, 'satuan' => 'unit', 'harga' => 1400000],
                ],
                'evidences' => [
                    ['name' => 'Quotation Mitroteknik.pdf'],
                ],
                'invoices' => [
                    [
                        'no' => 'INV-MTR-2025-005',
                        'tanggal' => now()->subDays(15),
                        'jumlah' => 16700000,
                        'jatuh_tempo' => now()->addDays(15),
                        'catatan' => 'Invoice full.',
                        'payments' => [
                            [
                                'tanggal_bayar' => now()->subDays(2),
                                'jumlah_bayar' => 16700000,
                                'metode' => 'Transfer Bank BRI',
                                'status' => PaymentApprovalStatus::Pending,
                            ],
                        ],
                    ],
                ],
                'catatan' => 'Pengadaan power tools workshop Makassar.',
            ],
            // 6. Approved + invoice full + payment partial (dibayar sebagian, sisanya belum)
            [
                'no' => 'PGD-2025-006',
                'vendor_code' => 'VDR-006',
                'cabang' => $priok,
                'tanggal' => now()->subDays(50),
                'status' => PengadaanApprovalStatus::Approved,
                'approved_at' => now()->subDays(48),
                'items' => [
                    ['nama_item' => 'Clamp Meter Fluke 376 FC', 'kategori' => 'Alat Ukur Listrik', 'qty' => 2, 'satuan' => 'unit', 'harga' => 9500000],
                ],
                'evidences' => [
                    ['name' => 'PO Clamp Meter.pdf'],
                ],
                'invoices' => [
                    [
                        'no' => 'INV-TST-2025-006',
                        'tanggal' => now()->subDays(45),
                        'jumlah' => 19000000,
                        'jatuh_tempo' => now()->subDays(15),
                        'catatan' => 'Invoice full.',
                        'payments' => [
                            [
                                'tanggal_bayar' => now()->subDays(40),
                                'jumlah_bayar' => 9500000,
                                'metode' => 'Transfer Bank BCA',
                                'status' => PaymentApprovalStatus::Approved,
                                'approved_at' => now()->subDays(38),
                            ],
                        ],
                    ],
                ],
                'catatan' => 'Pengadaan clamp meter, sisa 50% belum dibayar.',
            ],
            // 7. Rejected (dengan rejection_reason)
            [
                'no' => 'PGD-2025-007',
                'vendor_code' => 'VDR-007',
                'cabang' => $batam,
                'tanggal' => now()->subDays(10),
                'status' => PengadaanApprovalStatus::Rejected,
                'approved_at' => now()->subDays(8),
                'rejection_reason' => 'Anggaran Q1 sudah habis, mohon diajukan ulang di Q2.',
                'items' => [
                    ['nama_item' => 'Pressure Gauge WIKA 232.50 (Replacement)', 'kategori' => 'Alat Ukur Tekanan', 'qty' => 1, 'satuan' => 'unit', 'harga' => 1800000],
                ],
                'evidences' => [],
                'invoices' => [],
                'catatan' => 'Pengadaan pengganti pressure gauge rusak.',
            ],
            // 8. Approved + invoice overdue (jatuh tempo lewat, belum dibayar sama sekali)
            [
                'no' => 'PGD-2025-008',
                'vendor_code' => 'VDR-008',
                'cabang' => $surabaya,
                'tanggal' => now()->subDays(75),
                'status' => PengadaanApprovalStatus::Approved,
                'approved_at' => now()->subDays(73),
                'items' => [
                    ['nama_item' => 'Helm Safety SNI (Pack 10pcs)', 'kategori' => 'Safety Equipment', 'qty' => 10, 'satuan' => 'pack', 'harga' => 1500000],
                    ['nama_item' => 'Safety Shoes Steel Toe', 'kategori' => 'Safety Equipment', 'qty' => 20, 'satuan' => 'pasang', 'harga' => 650000],
                ],
                'evidences' => [
                    ['name' => 'Penawaran Safety Pro.pdf'],
                ],
                'invoices' => [
                    [
                        'no' => 'INV-SPI-2025-008',
                        'tanggal' => now()->subDays(70),
                        'jumlah' => 28000000,
                        'jatuh_tempo' => now()->subDays(40),
                        'catatan' => 'Invoice full, sudah lewat jatuh tempo.',
                        'payments' => [],
                    ],
                ],
                'catatan' => 'Pengadaan APD untuk workshop Surabaya. INVOICE OVERDUE.',
            ],
            // 9. Approved + invoice + payment rejected (perlu re-submit bukti transfer)
            [
                'no' => 'PGD-2025-009',
                'vendor_code' => 'VDR-009',
                'cabang' => $makassar,
                'tanggal' => now()->subDays(25),
                'status' => PengadaanApprovalStatus::Approved,
                'approved_at' => now()->subDays(23),
                'items' => [
                    ['nama_item' => 'Trolley Hydraulic 2 Ton', 'kategori' => 'Material Handling', 'qty' => 2, 'satuan' => 'unit', 'harga' => 7500000],
                ],
                'evidences' => [
                    ['name' => 'PO Trolley Hydraulic.pdf'],
                ],
                'invoices' => [
                    [
                        'no' => 'INV-MLM-2025-009',
                        'tanggal' => now()->subDays(20),
                        'jumlah' => 15000000,
                        'jatuh_tempo' => now()->addDays(10),
                        'catatan' => 'Invoice full.',
                        'payments' => [
                            [
                                'tanggal_bayar' => now()->subDays(5),
                                'jumlah_bayar' => 15000000,
                                'metode' => 'Transfer Bank BNI',
                                'status' => PaymentApprovalStatus::Rejected,
                                'approved_at' => now()->subDays(3),
                                'rejection_reason' => 'Bukti transfer tidak terlampir, mohon upload ulang.',
                            ],
                        ],
                    ],
                ],
                'catatan' => 'Pengadaan trolley hydraulic untuk gudang Banjarmasin.',
            ],
            // 10. Approved + multi-item + multi-evidence (kompleks, lunas)
            [
                'no' => 'PGD-2025-010',
                'vendor_code' => 'VDR-001',
                'cabang' => $priok,
                'tanggal' => now()->subDays(90),
                'status' => PengadaanApprovalStatus::Approved,
                'approved_at' => now()->subDays(88),
                'items' => [
                    ['nama_item' => 'Multimeter Digital Fluke 87V', 'kategori' => 'Alat Ukur Listrik', 'qty' => 1, 'satuan' => 'unit', 'harga' => 8500000],
                    ['nama_item' => 'Caliper Digital Mitutoyo 500-196-30', 'kategori' => 'Alat Ukur Dimensi', 'qty' => 1, 'satuan' => 'unit', 'harga' => 4200000],
                    ['nama_item' => 'Toolbox Besi 5 Laci', 'kategori' => 'Storage', 'qty' => 2, 'satuan' => 'unit', 'harga' => 2500000],
                    ['nama_item' => 'Multimeter Test Lead Set', 'kategori' => 'Aksesoris', 'qty' => 10, 'satuan' => 'set', 'harga' => 300000],
                ],
                'evidences' => [
                    ['name' => 'Surat Penawaran PT Sumber Alat Teknik.pdf'],
                    ['name' => 'Spesifikasi Teknis Lengkap.pdf'],
                    ['name' => 'BAST Pengadaan.pdf'],
                ],
                'invoices' => [
                    [
                        'no' => 'INV-SAT-2025-010-A',
                        'tanggal' => now()->subDays(85),
                        'jumlah' => 10000000,
                        'jatuh_tempo' => now()->subDays(55),
                        'catatan' => 'Invoice tahap 1 (DP).',
                        'payments' => [
                            [
                                'tanggal_bayar' => now()->subDays(80),
                                'jumlah_bayar' => 10000000,
                                'metode' => 'Transfer Bank BCA',
                                'status' => PaymentApprovalStatus::Approved,
                                'approved_at' => now()->subDays(78),
                            ],
                        ],
                    ],
                    [
                        'no' => 'INV-SAT-2025-010-B',
                        'tanggal' => now()->subDays(50),
                        'jumlah' => 11000000,
                        'jatuh_tempo' => now()->subDays(20),
                        'catatan' => 'Invoice tahap 2 (pelunasan).',
                        'payments' => [
                            [
                                'tanggal_bayar' => now()->subDays(45),
                                'jumlah_bayar' => 11000000,
                                'metode' => 'Transfer Bank BCA',
                                'status' => PaymentApprovalStatus::Approved,
                                'approved_at' => now()->subDays(43),
                            ],
                        ],
                    ],
                ],
                'catatan' => 'Pengadaan paket lengkap lab kalibrasi Priok. Multi-invoice & multi-payment.',
            ],
            // 11. Approved + invoice + multi-payment (lunas, 2x cicil)
            [
                'no' => 'PGD-2025-011',
                'vendor_code' => 'VDR-003',
                'cabang' => $belawan,
                'tanggal' => now()->subDays(45),
                'status' => PengadaanApprovalStatus::Approved,
                'approved_at' => now()->subDays(43),
                'items' => [
                    ['nama_item' => 'Oscilloscope Rigol DS1054Z', 'kategori' => 'Alat Ukur Elektronik', 'qty' => 1, 'satuan' => 'unit', 'harga' => 12000000],
                ],
                'evidences' => [
                    ['name' => 'PO Oscilloscope Rigol.pdf'],
                ],
                'invoices' => [
                    [
                        'no' => 'INV-MEI-2025-011',
                        'tanggal' => now()->subDays(40),
                        'jumlah' => 12000000,
                        'jatuh_tempo' => now()->subDays(10),
                        'catatan' => 'Invoice full.',
                        'payments' => [
                            [
                                'tanggal_bayar' => now()->subDays(35),
                                'jumlah_bayar' => 4000000,
                                'metode' => 'Transfer Bank Mandiri',
                                'status' => PaymentApprovalStatus::Approved,
                                'approved_at' => now()->subDays(33),
                            ],
                            [
                                'tanggal_bayar' => now()->subDays(20),
                                'jumlah_bayar' => 4000000,
                                'metode' => 'Transfer Bank Mandiri',
                                'status' => PaymentApprovalStatus::Approved,
                                'approved_at' => now()->subDays(18),
                            ],
                            [
                                'tanggal_bayar' => now()->subDays(5),
                                'jumlah_bayar' => 4000000,
                                'metode' => 'Transfer Bank Mandiri',
                                'status' => PaymentApprovalStatus::Approved,
                                'approved_at' => now()->subDays(3),
                            ],
                        ],
                    ],
                ],
                'catatan' => 'Pengadaan oscilloscope, dibayar 3x cicil.',
            ],
            // 12. Pending dengan evidence processing (file_status=processing)
            [
                'no' => 'PGD-2025-012',
                'vendor_code' => 'VDR-006',
                'cabang' => $priok,
                'tanggal' => now()->subDay(),
                'status' => PengadaanApprovalStatus::Pending,
                'items' => [
                    ['nama_item' => 'Anemometer Testo 405i', 'kategori' => 'Alat Ukur Lingkungan', 'qty' => 3, 'satuan' => 'unit', 'harga' => 2800000],
                ],
                'evidences' => [
                    ['name' => 'Quotation Testo.pdf', 'processing' => true],
                ],
                'invoices' => [],
                'catatan' => 'Pengadaan anemometer, evidence masih diproses.',
            ],
        ];

        DB::transaction(function () use ($scenarios, $vendors, $admin, $fileStorage, $disk) {
            foreach ($scenarios as $scenario) {
                $vendor = $vendors->get($scenario['vendor_code']);
                if (! $vendor) {
                    continue;
                }

                // Hitung total_biaya dari items
                $totalBiaya = 0;
                $itemsData = [];
                foreach ($scenario['items'] as $item) {
                    $subtotal = bcmul($item['harga'], (string) $item['qty'], 2);
                    $totalBiaya = bcadd($totalBiaya, $subtotal, 2);
                    $itemsData[] = [
                        'nama_item' => $item['nama_item'],
                        'kategori_item' => $item['kategori'],
                        'qty' => $item['qty'],
                        'satuan' => $item['satuan'],
                        'harga_satuan' => $item['harga'],
                        'subtotal' => $subtotal,
                    ];
                }

                $pengadaan = Pengadaan::firstOrCreate(
                    ['no_pengadaan' => $scenario['no']],
                    [
                        'vendor_id' => $vendor->id,
                        'cabang_id' => $scenario['cabang']?->id,
                        'tanggal_pengadaan' => $scenario['tanggal'],
                        'total_biaya' => $totalBiaya,
                        'status_approval' => $scenario['status']->value,
                        'approved_by' => $scenario['status'] !== PengadaanApprovalStatus::Pending ? $admin->id : null,
                        'approved_at' => $scenario['approved_at'] ?? null,
                        'rejection_reason' => $scenario['rejection_reason'] ?? null,
                        'catatan' => $scenario['catatan'] ?? null,
                    ]
                );

                // Items
                foreach ($itemsData as $itemData) {
                    $itemData['pengadaan_id'] = $pengadaan->id;
                    PengadaanItem::firstOrCreate(
                        [
                            'pengadaan_id' => $pengadaan->id,
                            'nama_item' => $itemData['nama_item'],
                            'qty' => $itemData['qty'],
                        ],
                        $itemData
                    );
                }

                // Evidences (seeder: tulis file dummy ke disk agar download bisa di-test)
                foreach ($scenario['evidences'] as $evidence) {
                    $isProcessing = $evidence['processing'] ?? false;

                    if ($isProcessing) {
                        // Evidence processing: tidak ada file fisik, status 'processing'
                        PengadaanEvidence::firstOrCreate(
                            [
                                'pengadaan_id' => $pengadaan->id,
                                'name' => $evidence['name'],
                            ],
                            [
                                'file_path' => null,
                                'file_name' => null,
                                'file_size' => null,
                                'file_status' => 'processing',
                                'file_processed_at' => null,
                            ]
                        );

                        continue;
                    }

                    // Evidence completed: bangun path konsisten via FileStorageService,
                    // lalu tulis file dummy ke disk agar download() bekerja.
                    $path = $fileStorage->buildPath(
                        'pengadaan-evidence',
                        [$pengadaan->no_pengadaan],
                        $evidence['name']
                    );
                    $dummyContent = "Seeded evidence file for {$pengadaan->no_pengadaan}: {$evidence['name']}";
                    Storage::disk($disk)->put($path, $dummyContent);
                    $size = strlen($dummyContent);

                    PengadaanEvidence::firstOrCreate(
                        [
                            'pengadaan_id' => $pengadaan->id,
                            'name' => $evidence['name'],
                        ],
                        [
                            'file_path' => $path,
                            'file_name' => $evidence['name'],
                            'file_size' => $size,
                            'file_status' => 'completed',
                            'file_processed_at' => now(),
                        ]
                    );
                }

                // Invoices + Payments (hanya untuk pengadaan approved)
                if ($scenario['status'] === PengadaanApprovalStatus::Approved) {
                    foreach ($scenario['invoices'] as $invoiceData) {
                        $invoice = Invoice::firstOrCreate(
                            ['pengadaan_id' => $pengadaan->id, 'no_invoice' => $invoiceData['no']],
                            [
                                'tanggal_invoice' => $invoiceData['tanggal'],
                                'jumlah' => $invoiceData['jumlah'],
                                'jatuh_tempo' => $invoiceData['jatuh_tempo'],
                                'catatan' => $invoiceData['catatan'] ?? null,
                                'file_status' => null, // seeder tidak upload file fisik invoice
                            ]
                        );

                        foreach ($invoiceData['payments'] as $paymentData) {
                            InvoicePayment::firstOrCreate(
                                [
                                    'invoice_id' => $invoice->id,
                                    'tanggal_bayar' => $paymentData['tanggal_bayar'],
                                    'jumlah_bayar' => $paymentData['jumlah_bayar'],
                                ],
                                [
                                    'metode_bayar' => $paymentData['metode'] ?? null,
                                    'status_approval' => $paymentData['status']->value,
                                    'approved_by' => $paymentData['status'] === PaymentApprovalStatus::Approved ? $admin->id : null,
                                    'approved_at' => $paymentData['approved_at'] ?? null,
                                    'rejection_reason' => $paymentData['rejection_reason'] ?? null,
                                    'file_status' => null, // seeder tidak upload bukti transfer fisik
                                ]
                            );
                        }
                    }
                }
            }
        });
    }
}
