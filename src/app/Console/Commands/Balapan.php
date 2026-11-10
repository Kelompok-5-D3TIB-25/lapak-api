<?php

private function jalankan(string $nama): bool
 {
 $skenario = self::muat($nama);
 $jumlah = (int) ($this->option('proses') ?: $skenario->proses());
 $this->newLine();
 $this->line("<options=bold>{$nama}</>: {$skenario->keterangan()}");
 $konteks = $skenario->siapkan();
 // Setiap anak perlu waktu untuk menyalakan Laravel. Semuanya menunggu sampai detik ini,
 // lalu berangkat bersama. Tanpa garis mulai, anak pertama sudah selesai sebelum
 // anak terakhir lahir, dan tidak ada yang berlomba.
 $konteks['mulai'] = microtime(true) + max(2.0, $jumlah * 0.3);
 $muatan = base64_encode(json_encode($konteks, JSON_THROW_ON_ERROR));
 $anak = [];
 for ($nomor = 1; $nomor <= $jumlah; $nomor++) {
 $proses = new Process(
 [PHP_BINARY, 'artisan', 'lapak:balapan-anak', $nama, (string) $nomor, $muatan],
 base_path(),
 );
 $proses->setTimeout(120);
 $proses->start();
 $anak[] = $proses;
 }
 $hasil = [];
 foreach ($anak as $proses) {
 $proses->wait();
 $keluaran = trim($proses->getOutput());
 $hasil[] = $keluaran !== ''
 ? $keluaran
 : 'GALAT '.Str::limit(trim($proses->getErrorOutput()), 90);
 }
 $rekap = array_count_values($hasil);
 ksort($rekap);
 $this->table(
 ['Hasil', 'Jumlah proses'],
 array_map(null, array_keys($rekap), array_values($rekap)),
 );
 $pelanggaran = $skenario->periksa($konteks, $hasil);
 foreach ($pelanggaran as $pesan) {
 $this->error('PELANGGARAN: '.$pesan);
 }
 if ($pelanggaran === []) {
$this->info("LULUS: {$jumlah} proses serentak, tidak ada aturan yang dilanggar.");
 }
 return $pelanggaran === [];
 }