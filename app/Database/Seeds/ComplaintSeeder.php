<?php

declare(strict_types=1);

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use DateTimeImmutable;

/**
 * Aduan demo berlokasi nyata di Kota Bandung beserta riwayat status,
 * tanggapan, penilaian, dan foto bukti.
 */
class ComplaintSeeder extends Seeder
{
    private const CITY = 'Kota Bandung';

    /**
     * @var list<array{address: string, village: string, district: string, lat: float, lng: float}>
     */
    private const LOCATIONS = [
        ['address' => 'Jl. Asia Afrika depan Gedung Merdeka', 'village' => 'Braga', 'district' => 'Sumur Bandung', 'lat' => -6.9218000, 'lng' => 107.6109000],
        ['address' => 'Jl. L.L.R.E. Martadinata (Riau) dekat Museum Konferensi Asia Afrika', 'village' => 'Citarum', 'district' => 'Bandung Wetan', 'lat' => -6.9044000, 'lng' => 107.6186000],
        ['address' => 'Jl. Ir. H. Juanda (Dago) depan kampus', 'village' => 'Dago', 'district' => 'Coblong', 'lat' => -6.8852000, 'lng' => 107.6136000],
        ['address' => 'Jl. Dr. Djunjunan (Pasteur) arah bandara', 'village' => 'Sukajadi', 'district' => 'Sukajadi', 'lat' => -6.8938000, 'lng' => 107.5896000],
        ['address' => 'Jl. Soekarno Hatta simpang Cibaduyut', 'village' => 'Cibaduyut', 'district' => 'Bojongloa Kidul', 'lat' => -6.9536000, 'lng' => 107.5900000],
        ['address' => 'Jl. Kiaracondong dekat Stasiun Kiaracondong', 'village' => 'Kiaracondong', 'district' => 'Kiaracondong', 'lat' => -6.9246000, 'lng' => 107.6423000],
        ['address' => 'Jl. Buah Batu simpang Turangga', 'village' => 'Turangga', 'district' => 'Lengkong', 'lat' => -6.9418000, 'lng' => 107.6315000],
        ['address' => 'Jl. Setiabudi depan Ledeng', 'village' => 'Gegerkalong', 'district' => 'Sukasari', 'lat' => -6.8728000, 'lng' => 107.5885000],
        ['address' => 'Jl. Terusan Jakarta (Antapani)', 'village' => 'Antapani', 'district' => 'Antapani', 'lat' => -6.9126000, 'lng' => 107.6607000],
        ['address' => 'Jl. Kopo sayati', 'village' => 'Kopo', 'district' => 'Bojongloa Kaler', 'lat' => -6.9455000, 'lng' => 107.5798000],
        ['address' => 'Terminal Leuwipanjang area keberangkatan', 'village' => 'Situsaeur', 'district' => 'Bojongloa Kidul', 'lat' => -6.9450000, 'lng' => 107.5890000],
        ['address' => 'Pasar Caringin blok A', 'village' => 'Sukamulya', 'district' => 'Bojongloa Kidul', 'lat' => -6.9489000, 'lng' => 107.5811000],
        ['address' => 'Taman Balai Kota Bandung', 'village' => 'Citarum', 'district' => 'Bandung Wetan', 'lat' => -6.9092000, 'lng' => 107.6172000],
        ['address' => 'Puskesmas Garuda, Jl. Dadali', 'village' => 'Garuda', 'district' => 'Andir', 'lat' => -6.9131000, 'lng' => 107.5812000],
        ['address' => 'Jl. Cihapit gang 3', 'village' => 'Cihapit', 'district' => 'Bandung Wetan', 'lat' => -6.9047000, 'lng' => 107.6265000],
        ['address' => 'Jl. Cihampelas depan Cihampelas Walk', 'village' => 'Tamansari', 'district' => 'Bandung Wetan', 'lat' => -6.8982000, 'lng' => 107.6049000],
        ['address' => 'Jl. Ahmad Yani flyover Cicaheum', 'village' => 'Cicaheum', 'district' => 'Kiaracondong', 'lat' => -6.9225000, 'lng' => 107.6528000],
        ['address' => 'Jl. Sadang Serang dekat kampus UPI', 'village' => 'Sadang Serang', 'district' => 'Coblong', 'lat' => -6.8978000, 'lng' => 107.6188000],
        ['address' => 'Jl. Cibaduyut Raya sentra sepatu', 'village' => 'Cibaduyut', 'district' => 'Bojongloa Kidul', 'lat' => -6.9551000, 'lng' => 107.5863000],
        ['address' => 'Jl. Margahayu Raya sektor 5', 'village' => 'Sekejati', 'district' => 'Buahbatu', 'lat' => -6.9555000, 'lng' => 107.6360000],
    ];

    /**
     * Judul & deskripsi aduan per kategori.
     *
     * @var array<string, list<array{title: string, description: string}>>
     */
    private const TEMPLATES = [
        'Jalan & Jembatan Rusak' => [
            [
                'title'       => 'Jalan berlubang cukup dalam membahayakan pengendara',
                'description' => 'Terdapat beberapa lubang cukup dalam di badan jalan. Saat hujan lubang tergenang air sehingga sulit dilihat pengendara. Sudah ada pengendara sepeda motor yang terjatuh di lokasi ini. Mohon segera diperbaiki sebelum menimbulkan korban lain.',
            ],
            [
                'title'       => 'Aspal jalan mengelupas dan bergelombang',
                'description' => 'Permukaan aspal mengelupas sepanjang kurang lebih 20 meter dan jalan menjadi bergelombang. Kondisi ini sangat mengganggu arus lalu lintas terutama pada jam sibuk pagi dan sore hari.',
            ],
        ],
        'Drainase & Banjir' => [
            [
                'title'       => 'Saluran drainase tersumbat menyebabkan genangan',
                'description' => 'Saluran air di sisi jalan tertutup sampah dan endapan lumpur sehingga air tidak mengalir. Setiap hujan deras jalan tergenang setinggi mata kaki dan masuk ke halaman rumah warga.',
            ],
            [
                'title'       => 'Got di depan sekolah meluap saat hujan',
                'description' => 'Got yang tidak pernah dikeruk meluap setiap hujan. Air kotor mengalir ke halaman sekolah dan menimbulkan bau tidak sedap. Mohon dilakukan pengerukan secara berkala.',
            ],
        ],
        'Penerangan Jalan Umum' => [
            [
                'title'       => 'Lampu penerangan jalan mati lebih dari satu bulan',
                'description' => 'Tiga titik lampu penerangan jalan mati sejak sebulan terakhir. Jalan menjadi sangat gelap pada malam hari dan rawan tindak kejahatan serta kecelakaan.',
            ],
            [
                'title'       => 'Lampu PJU menyala pada siang hari',
                'description' => 'Lampu penerangan jalan tetap menyala pada siang hari. Selain pemborosan energi listrik, hal ini mengganggu dan kemungkinan sensor atau timer lampu mengalami kerusakan.',
            ],
        ],
        'Transportasi Umum' => [
            [
                'title'       => 'Angkutan umum tidak berhenti di halte',
                'description' => 'Angkutan umum kerap menaikkan dan menurunkan penumpang di sembarang tempat sehingga memicu kemacetan. Petugas di lapangan diharapkan melakukan penertiban pada jam sibuk.',
            ],
            [
                'title'       => 'Halte bus rusak dan tidak terawat',
                'description' => 'Atap halte bocor dan beberapa bangku penumpang patah. Fasilitas ini tidak layak dipakai terutama saat hujan, penumpang terpaksa menunggu di bawah.',
            ],
        ],
        'Rambu & Marka Jalan' => [
            [
                'title'       => 'Marka zebra cross sudah pudar',
                'description' => 'Marka zebra cross di depan sekolah sudah pudar sehingga pengendara tidak melihat keberadaan fasilitas penyeberangan. Sangat berisiko bagi anak sekolah pada jam masuk dan pulang.',
            ],
            [
                'title'       => 'Rambu lalu lintas tumbang',
                'description' => 'Rambu petunjuk arah tumbang setelah tertabrak. Saat ini rambu tergeletak di tepi jalan dan dapat membahayakan pengendara, mohon segera dipasang kembali.',
            ],
        ],
        'Sampah & Kebersihan' => [
            [
                'title'       => 'Tumpukan sampah di bahu jalan tidak diangkut',
                'description' => 'Sampah rumah tangga menumpuk di bahu jalan selama lebih dari lima hari dan belum diangkut. Bau menyengat dan mengundang lalat serta tikus di sekitar permukiman.',
            ],
            [
                'title'       => 'Tempat sampah umum penuh dan rusak',
                'description' => 'Bak sampah umum sudah penuh dan sebagian rusak sehingga sampah berserakan ke jalan. Diharapkan ada penambahan bak sampah dan pengangkutan yang lebih rutin.',
            ],
        ],
        'Taman & Ruang Terbuka Hijau' => [
            [
                'title'       => 'Peralatan taman rusak dan rumput tidak dipangkas',
                'description' => 'Beberapa ayunan dan perosotan sudah patah namun masih digunakan anak-anak. Rumput juga tinggi dan menjadi sarang nyamuk. Mohon dilakukan perbaikan dan pemangkasan.',
            ],
            [
                'title'       => 'Lampu taman mati sehingga rawan',
                'description' => 'Lampu taman di sisi timur mati sejak lama. Kawasan taman menjadi gelap pada malam hari dan rawan digunakan untuk aktivitas yang mengganggu ketertiban.',
            ],
        ],
        'Pelayanan Administrasi Kependudukan' => [
            [
                'title'       => 'Antrean pelayanan sangat panjang dan lambat',
                'description' => 'Loket pelayanan hanya dibuka satu dari tiga yang tersedia sehingga antrean sangat panjang. Warga harus menunggu lebih dari tiga jam untuk satu berkas. Mohon menambah petugas loket.',
            ],
            [
                'title'       => 'Petugas tidak berada di tempat pada jam pelayanan',
                'description' => 'Saya datang pada jam pelayanan resmi namun loket kosong tanpa keterangan. Warga diminta kembali keesokan harinya tanpa penjelasan yang memadai.',
            ],
        ],
        'Dokumen Kependudukan' => [
            [
                'title'       => 'Permohonan akta kelahiran belum selesai 2 bulan',
                'description' => 'Permohonan akta kelahiran anak saya sudah diajukan dua bulan lalu dengan berkas lengkap, namun hingga kini belum selesai tanpa informasi perkembangan. Mohon diproses segera karena dibutuhkan untuk pendaftaran sekolah.',
            ],
            [
                'title'       => 'Data nama pada KTP tidak sesuai',
                'description' => 'Penulisan nama pada KTP tidak sesuai dengan dokumen resmi lainnya sehingga menyulitkan pengurusan keperluan lain. Mohon dilakukan perbaikan data.',
            ],
        ],
        'Fasilitas Kesehatan' => [
            [
                'title'       => 'Puskesmas kekurangan stok obat dasar',
                'description' => 'Beberapa kali datang ke puskesmas, obat dasar untuk demam dan maag selalu kosong. Pasien akhirnya harus membeli sendiri ke apotek terdekat, mohon stok obat dilengkapi.',
            ],
            [
                'title'       => 'Air bersih puskesmas bermasalah',
                'description' => 'Air di puskesmas kurang lancar dan sempat keruh. Hal ini tentu mengganggu pelayanan kesehatan, terutama kebersihan ruang tindakan dan toilet pasien.',
            ],
        ],
    ];

    /**
     * @var array<string, int>
     */
    private const STATUS_PLAN = [
        'menunggu'     => 6,
        'diverifikasi' => 5,
        'diproses'     => 6,
        'selesai'      => 8,
        'ditolak'      => 2,
    ];

    /** @var array<int, int> */
    private array $masyarakatIds = [];

    /**
     * @var array<int, list<int>>
     */
    private array $pemdaIdsByAgency = [];

    public function run(): void
    {
        mt_srand(20261019);

        $this->loadUsers();

        $categories = $this->db->table('categories')
            ->select('categories.id, categories.agency_id, categories.name')
            ->where('categories.deleted_at', null)
            ->orderBy('categories.id', 'ASC')
            ->get()
            ->getResultArray();

        if ($categories === [] || $this->masyarakatIds === []) {
            return;
        }

        $sequence = [];

        foreach (self::STATUS_PLAN as $status => $count) {
            for ($i = 0; $i < $count; $i++) {
                $sequence[] = $status;
            }
        }

        $index = 0;

        foreach ($sequence as $status) {
            $category = $categories[$index % count($categories)];
            $templates = self::TEMPLATES[(string) $category['name']] ?? [];
            $template  = $templates[intdiv($index, count($categories)) % max(1, count($templates))];
            $location  = self::LOCATIONS[$index % count(self::LOCATIONS)];
            $reporter  = $this->masyarakatIds[$index % count($this->masyarakatIds)];
            $agencyId  = (int) $category['agency_id'];

            $this->createComplaint(
                $index,
                $status,
                $reporter,
                (int) $category['id'],
                $agencyId,
                $template,
                $location,
            );

            $index++;
        }
    }

    /**
     * @param array{title: string, description: string} $template
     * @param array{address: string, village: string, district: string, lat: float, lng: float} $location
     */
    private function createComplaint(
        int $index,
        string $status,
        int $reporterId,
        int $categoryId,
        int $agencyId,
        array $template,
        array $location,
    ): void {
        $createdAt  = $this->randomCreatedAt($index);
        $complaintId = null;

        $this->db->table('complaints')->insert([
            'ticket_code'    => $this->ticketCode($createdAt, $index),
            'user_id'        => $reporterId,
            'category_id'    => $categoryId,
            'agency_id'      => $agencyId,
            'title'          => $template['title'],
            'description'    => $template['description'],
            'incident_date'  => (new DateTimeImmutable($createdAt))->modify('-' . mt_rand(1, 7) . ' days')->format('Y-m-d'),
            'latitude'       => $location['lat'],
            'longitude'      => $location['lng'],
            'address_detail' => $location['address'],
            'village'        => $location['village'],
            'district'       => $location['district'],
            'city'           => self::CITY,
            'hide_identity'  => $index % 7 === 0 ? 1 : 0,
            'status'         => 'menunggu',
            'created_at'     => $createdAt,
            'updated_at'     => $createdAt,
        ]);

        $complaintId = (int) $this->db->insertID();

        if ($complaintId <= 0) {
            return;
        }

        $pemdaId = $this->pemdaFor($agencyId);

        $lastAt = $this->writeStatusHistory($complaintId, $status, $reporterId, $pemdaId, $createdAt);

        $this->writeResponses($complaintId, $status, $reporterId, $pemdaId, $createdAt, $agencyId);
        $this->writeAttachments($complaintId, $reporterId, $pemdaId, $status, $index);

        $rating         = null;
        $ratingComment  = null;

        if ($status === 'selesai' && $index % 4 !== 0) {
            $rating = mt_rand(3, 5);
            $ratingComment = match ($rating) {
                5 => 'Terima kasih, penanganannya cepat dan hasilnya memuaskan.',
                4 => 'Sudah cukup baik, hanya komunikasi perkembangannya bisa ditingkatkan.',
                default => 'Perbaikan sudah dilakukan, namun harap lebih cepat pada laporan berikutnya.',
            };
        }

        $this->db->table('complaints')->where('id', $complaintId)->update([
            'status'          => $status,
            'rejected_reason' => $status === 'ditolak'
                ? 'Lokasi yang dilaporkan berada di luar kewenangan instansi dan telah diteruskan ke instansi terkait.'
                : null,
            'rating'          => $rating,
            'rating_comment'  => $ratingComment,
            'updated_at'      => $lastAt,
        ]);
    }

    private function writeStatusHistory(int $complaintId, string $status, int $reporterId, ?int $pemdaId, string $createdAt): string
    {
        $entries = [
            [$createdAt, $reporterId, null, 'menunggu', 'Aduan dibuat oleh pelapor.'],
        ];

        $cursor = new DateTimeImmutable($createdAt);

        $steps = match ($status) {
            'diverifikasi' => [['diverifikasi', 1, 'Aduan diverifikasi dan sesuai dengan kewenangan instansi.']],
            'diproses'     => [
                ['diverifikasi', 1, 'Aduan diverifikasi dan sesuai dengan kewenangan instansi.'],
                ['diproses', 3, 'Petugas sudah dijadwalkan ke lokasi untuk penanganan.'],
            ],
            'selesai'      => [
                ['diverifikasi', 1, 'Aduan diverifikasi dan sesuai dengan kewenangan instansi.'],
                ['diproses', 3, 'Petugas sudah dijadwalkan ke lokasi untuk penanganan.'],
                ['selesai', 7, 'Perbaikan telah dilaksanakan dan pekerjaan selesai.'],
            ],
            'ditolak'      => [
                ['ditolak', 2, 'Lokasi yang dilaporkan berada di luar kewenangan instansi.'],
            ],
            default        => [],
        };

        $previous = 'menunggu';

        foreach ($steps as [$to, $days, $note]) {
            $cursor = $cursor->modify('+' . $days . ' days');
            $at     = $cursor->format('Y-m-d H:i:s');

            $entries[] = [$at, $pemdaId ?? $reporterId, $previous, $to, $note];
            $previous  = $to;
        }

        $rows = [];

        foreach ($entries as [$at, $actor, $from, $to, $note]) {
            $rows[] = [
                'complaint_id' => $complaintId,
                'changed_by'   => $actor,
                'from_status'  => $from,
                'to_status'    => $to,
                'note'         => $note,
                'created_at'   => $at,
            ];
        }

        $this->db->table('complaint_status_histories')->insertBatch($rows);

        return (string) end($entries)[0];
    }

    private function writeResponses(
        int $complaintId,
        string $status,
        int $reporterId,
        ?int $pemdaId,
        string $createdAt,
        int $agencyId,
    ): void {
        if ($pemdaId === null || $status === 'menunggu') {
            return;
        }

        $cursor = new DateTimeImmutable($createdAt);
        $rows   = [];

        $rows[] = [
            'complaint_id' => $complaintId,
            'user_id'      => $pemdaId,
            'message'      => 'Terima kasih atas laporannya. Aduan Anda sudah kami terima dan sedang kami tindak lanjuti oleh petugas terkait.',
            'created_at'   => $cursor->modify('+1 day')->format('Y-m-d H:i:s'),
        ];

        if (in_array($status, ['diproses', 'selesai'], true)) {
            $rows[] = [
                'complaint_id' => $complaintId,
                'user_id'      => $pemdaId,
                'message'      => 'Petugas kami sudah melakukan pengecekan di lokasi sesuai koordinat yang dilaporkan. Proses perbaikan sedang berjalan.',
                'created_at'   => $cursor->modify('+4 days')->format('Y-m-d H:i:s'),
            ];
        }

        if ($status === 'selesai') {
            $rows[] = [
                'complaint_id' => $complaintId,
                'user_id'      => $pemdaId,
                'message'      => 'Pekerjaan telah selesai dilaksanakan. Silakan dicek kembali dan berikan penilaian Anda atas pelayanan ini.',
                'created_at'   => $cursor->modify('+7 days')->format('Y-m-d H:i:s'),
            ];
        }

        if (mt_rand(0, 1) === 1) {
            $rows[] = [
                'complaint_id' => $complaintId,
                'user_id'      => $reporterId,
                'message'      => 'Terima kasih atas responnya, mohon diinformasikan kembali jika penanganan sudah selesai.',
                'created_at'   => $cursor->modify('+5 days')->format('Y-m-d H:i:s'),
            ];
        }

        $this->db->table('complaint_responses')->insertBatch($rows);
    }

    private function writeAttachments(int $complaintId, int $reporterId, ?int $pemdaId, string $status, int $index): void
    {
        $count = $index % 3 === 0 ? 2 : 1;

        for ($i = 0; $i < $count; $i++) {
            $stored = $this->createPlaceholderImage($complaintId, 'bukti_aduan', $index + $i);

            if ($stored === null) {
                return;
            }

            $this->db->table('complaint_attachments')->insert([
                'complaint_id'  => $complaintId,
                'uploaded_by'   => $reporterId,
                'type'          => 'bukti_aduan',
                'file_path'     => $stored,
                'original_name' => 'foto-aduan-' . ($i + 1) . '.jpg',
                'mime_type'     => 'image/jpeg',
                'size'          => (int) (filesize($this->uploadRoot() . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $stored)) ?: 0),
                'created_at'    => date('Y-m-d H:i:s'),
            ]);
        }

        if ($status === 'selesai' && $pemdaId !== null) {
            $stored = $this->createPlaceholderImage($complaintId, 'bukti_penyelesaian', $index + 100);

            if ($stored !== null) {
                $this->db->table('complaint_attachments')->insert([
                    'complaint_id'  => $complaintId,
                    'uploaded_by'   => $pemdaId,
                    'type'          => 'bukti_penyelesaian',
                    'file_path'     => $stored,
                    'original_name' => 'bukti-selesai.jpg',
                    'mime_type'     => 'image/jpeg',
                    'size'          => (int) (filesize($this->uploadRoot() . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $stored)) ?: 0),
                    'created_at'    => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }

    private function createPlaceholderImage(int $complaintId, string $type, int $seed): ?string
    {
        if (! function_exists('imagecreatetruecolor')) {
            return null;
        }

        $relative = sprintf('complaints/%s/%s/%d_%s.jpg', date('Y'), date('m'), $complaintId, bin2hex(random_bytes(8)));
        $absolute = $this->uploadRoot() . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);

        $directory = dirname($absolute);

        if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
            return null;
        }

        $image = imagecreatetruecolor(800, 600);

        if ($image === false) {
            return null;
        }

        $palette = $type === 'bukti_penyelesaian'
            ? [[32, 120, 90], [46, 160, 120], [18, 90, 70]]
            : [[120, 110, 100], [150, 140, 130], [95, 88, 80]];

        $offset = $seed % 3;

        for ($y = 0; $y < 600; $y++) {
            $ratio = $y / 600;
            $from  = $palette[$offset % 3];
            $to    = $palette[($offset + 1) % 3];
            $color = imagecolorallocate(
                $image,
                (int) ($from[0] + ($to[0] - $from[0]) * $ratio),
                (int) ($from[1] + ($to[1] - $from[1]) * $ratio),
                (int) ($from[2] + ($to[2] - $from[2]) * $ratio),
            );
            imageline($image, 0, $y, 800, $y, $color);
        }

        $white = imagecolorallocate($image, 255, 255, 255);
        $dark  = imagecolorallocate($image, 30, 30, 30);

        $label = $type === 'bukti_penyelesaian'
            ? 'DOKUMENTASI PENYELESAIAN'
            : 'DOKUMENTASI ADUAN WARGA';

        imagestring($image, 5, 30, 40, $label, $white);
        imagestring($image, 4, 30, 80, 'Kota Bandung - Contoh foto demo SIPALA', $white);
        imagestring($image, 3, 30, 560, 'Foto contoh otomatis, bukan dokumentasi nyata.', $dark);

        imagejpeg($image, $absolute, 80);
        imagedestroy($image);

        return $relative;
    }

    private function ticketCode(string $createdAt, int $index): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $token    = '';

        for ($i = 0; $i < 6; $i++) {
            $token .= $alphabet[mt_rand(0, strlen($alphabet) - 1)];
        }

        return sprintf('SIP-%s-%s', date('Ymd', (int) strtotime($createdAt)), $token);
    }

    private function randomCreatedAt(int $index): string
    {
        $daysAgo = 2 + ($index * 6) % 170;
        $hour    = mt_rand(7, 20);
        $minute  = mt_rand(0, 59);

        return (new DateTimeImmutable('today'))
            ->modify('-' . $daysAgo . ' days')
            ->setTime($hour, $minute)
            ->format('Y-m-d H:i:s');
    }

    private function loadUsers(): void
    {
        $rows = $this->db->table('users')
            ->select('id, role, agency_id')
            ->where('deleted_at', null)
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        foreach ($rows as $row) {
            if ($row['role'] === 'masyarakat') {
                $this->masyarakatIds[] = (int) $row['id'];

                continue;
            }

            if ($row['role'] === 'pemda') {
                $this->pemdaIdsByAgency[(int) $row['agency_id']][] = (int) $row['id'];
            }
        }
    }

    private function pemdaFor(int $agencyId): ?int
    {
        $candidates = $this->pemdaIdsByAgency[$agencyId] ?? [];

        return $candidates === [] ? null : $candidates[mt_rand(0, count($candidates) - 1)];
    }

    private function uploadRoot(): string
    {
        return WRITEPATH . 'uploads';
    }
}
