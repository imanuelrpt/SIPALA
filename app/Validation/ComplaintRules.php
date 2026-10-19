<?php

declare(strict_types=1);

namespace App\Validation;

use Config\Sipala;

/**
 * Aturan validasi modul pengaduan (masyarakat, pemda, admin).
 *
 * Rentang koordinat diambil dari Config\Sipala agar tidak ada angka ajaib.
 */
final class ComplaintRules
{
    /** @var array<string, string> */
    public array $store;

    /** @var array<string, string> */
    public array $statusUpdate = [
        'status'             => 'required|in_list[menunggu,diverifikasi,diproses,selesai,ditolak]',
        'note'               => 'permit_empty|max_length[1000]',
        'rejected_reason'    => 'permit_empty|max_length[1000]',
        'resolution_note'    => 'permit_empty|max_length[1000]',
    ];

    /** @var array<string, string> */
    public array $response = [
        'message' => 'required|min_length[3]|max_length[2000]',
    ];

    /** @var array<string, string> */
    public array $rating = [
        'rating'         => 'required|is_natural_no_zero|less_than_equal_to[5]',
        'rating_comment' => 'permit_empty|max_length[1000]',
    ];

    /** @var array<string, string> */
    public array $disposition = [
        'agency_id' => 'required|is_natural_no_zero',
        'note'      => 'permit_empty|max_length[1000]',
    ];

    /** @var array<string, string> */
    public array $filters = [
        'status'      => 'permit_empty|in_list[menunggu,diverifikasi,diproses,selesai,ditolak]',
        'category_id' => 'permit_empty|is_natural_no_zero',
        'agency_id'   => 'permit_empty|is_natural_no_zero',
        'date_from'   => 'permit_empty|valid_date[Y-m-d]',
        'date_to'     => 'permit_empty|valid_date[Y-m-d]',
        'search'      => 'permit_empty|max_length[100]',
        'page'        => 'permit_empty|is_natural_no_zero',
    ];

    public function __construct()
    {
        /** @var Sipala $sipala */
        $sipala = config(Sipala::class);

        $this->store = [
            'title'         => 'required|min_length[10]|max_length[150]',
            'category_id'   => 'required|is_natural_no_zero',
            'description'   => 'required|min_length[20]|max_length[5000]',
            'incident_date' => 'required|valid_date[Y-m-d]|not_future_date',
            'latitude'      => 'required|coordinate_range[' . $sipala->minLatitude . ',' . $sipala->maxLatitude . ']',
            'longitude'     => 'required|coordinate_range[' . $sipala->minLongitude . ',' . $sipala->maxLongitude . ']',
            'address_detail' => 'permit_empty|max_length[255]',
            'village'       => 'permit_empty|max_length[100]',
            'district'      => 'permit_empty|max_length[100]',
            'city'          => 'permit_empty|max_length[100]',
            'hide_identity' => 'permit_empty|in_list[0,1]',
        ];
    }

    /**
     * @var array<string, array<string, string>>
     */
    public array $messages = [
        'title' => [
            'required'   => 'Judul aduan wajib diisi.',
            'min_length' => 'Judul aduan minimal 10 karakter.',
            'max_length' => 'Judul aduan maksimal 150 karakter.',
        ],
        'category_id' => [
            'required'           => 'Kategori aduan wajib dipilih.',
            'is_natural_no_zero' => 'Kategori aduan tidak valid.',
        ],
        'description' => [
            'required'   => 'Deskripsi aduan wajib diisi.',
            'min_length' => 'Deskripsi aduan minimal 20 karakter.',
            'max_length' => 'Deskripsi aduan maksimal 5000 karakter.',
        ],
        'incident_date' => [
            'required'         => 'Tanggal kejadian wajib diisi.',
            'valid_date'       => 'Format tanggal kejadian tidak valid.',
            'not_future_date'  => 'Tanggal kejadian tidak boleh melebihi hari ini.',
        ],
        'latitude' => [
            'required'          => 'Titik lokasi wajib dipilih pada peta.',
            'coordinate_range'  => 'Latitude harus berada di wilayah Indonesia (-11 s/d 6).',
        ],
        'longitude' => [
            'required'          => 'Titik lokasi wajib dipilih pada peta.',
            'coordinate_range'  => 'Longitude harus berada di wilayah Indonesia (95 s/d 141).',
        ],
        'address_detail' => [
            'max_length' => 'Detail alamat maksimal 255 karakter.',
        ],
        'district' => [
            'max_length' => 'Kecamatan maksimal 100 karakter.',
        ],
        'village' => [
            'max_length' => 'Kelurahan maksimal 100 karakter.',
        ],
        'city' => [
            'max_length' => 'Kabupaten/Kota maksimal 100 karakter.',
        ],
        'status' => [
            'required' => 'Status wajib dipilih.',
            'in_list'  => 'Status tidak dikenal.',
        ],
        'message' => [
            'required'   => 'Tanggapan wajib diisi.',
            'min_length' => 'Tanggapan minimal 3 karakter.',
            'max_length' => 'Tanggapan maksimal 2000 karakter.',
        ],
        'rating' => [
            'required'           => 'Penilaian wajib dipilih.',
            'is_natural_no_zero' => 'Penilaian tidak valid.',
            'less_than_equal_to' => 'Penilaian maksimal 5 bintang.',
        ],
        'rating_comment' => [
            'max_length' => 'Komentar penilaian maksimal 1000 karakter.',
        ],
        'agency_id' => [
            'required'           => 'Instansi tujuan wajib dipilih.',
            'is_natural_no_zero' => 'Instansi tidak valid.',
        ],
        'date_from' => [
            'valid_date' => 'Tanggal awal tidak valid.',
        ],
        'date_to' => [
            'valid_date' => 'Tanggal akhir tidak valid.',
        ],
        'search' => [
            'max_length' => 'Kata kunci pencarian maksimal 100 karakter.',
        ],
    ];
}
