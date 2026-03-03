<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class GuideSeeder extends Seeder
{
    public function run(): void
    {
        $guides = [
            [
                'guide_id'     => 11,
                'slug'         => 'pola-makan-sehat-kunci-energi-fokus-dan-kualitas-hidup-GYitWD',
                'published_at' => '2026-02-13 17:11:06',
                'title'        => 'Pola Makan Sehat: Kunci Energi, Fokus, dan Kualitas Hidup',
                'content'      => <<<'HTML'
<p><span>Pola makan bukan sekadar soal kenyang. Apa yang kita konsumsi setiap hari berpengaruh langsung pada energi, konsentrasi, suasana hati, hingga risiko penyakit jangka panjang. Banyak orang baru memperhatikan pola makan setelah muncul gangguan kesehatan, padahal kebiasaan makan yang baik seharusnya dibangun sejak dini sebagai investasi hidup.</span></p>

<p><br></p>

<p><span>Menurut rekomendasi dari World Health Organization, pola makan sehat adalah pola makan yang seimbang, beragam, dan cukup untuk memenuhi kebutuhan tubuh tanpa berlebihan. Artinya, kita perlu memperhatikan kualitas makanan, bukan hanya jumlahnya.</span></p>

<p><br></p>

<p><b><strong>Mengapa Pola Makan Sehat Penting?</strong></b></p>

<p><span>Tubuh manusia bekerja seperti mesin yang membutuhkan bahan bakar berkualitas. Jika bahan bakarnya buruk, tinggi gula, lemak jenuh, dan makanan ultra-proses maka kinerja tubuh ikut menurun. Pola makan yang buruk dapat meningkatkan risiko obesitas, diabetes, penyakit jantung, dan gangguan metabolisme.</span></p>

<p><span>Sebaliknya, pola makan sehat membantu:</span></p>

<ul><li><span>Menjaga berat badan ideal</span></li><li><span>Meningkatkan energi harian</span></li><li><span>Memperkuat sistem imun</span></li><li><span>Menjaga kesehatan otak</span></li><li><span>Menstabilkan emosi dan fokus</span></li></ul>

<p><br></p>

<p><b><strong>Prinsip Dasar Pola Makan Seimbang</strong></b></p>

<p><span>Pola makan sehat tidak berarti harus mahal atau rumit. Yang penting adalah keseimbangan. Berikut prinsip sederhananya:</span></p>

<p><b><strong>1. Perbanyak makanan alami</strong></b><br><span>Sayur, buah, biji-bijian, kacang-kacangan, dan protein alami harus menjadi fondasi utama. Semakin sedikit proses industri, semakin baik.</span></p>

<p><b><strong>2. Atur porsi, bukan sekadar jenis</strong></b><br><span>Makanan sehat tetap perlu dikontrol porsinya. Makan berlebihan, bahkan makanan sehat, tetap bisa berdampak buruk.</span></p>

<p><b><strong>3. Batasi gula, garam, dan lemak berlebih</strong></b><br><span>Konsumsi berlebihan dapat memicu penyakit kronis. Biasakan membaca label nutrisi.</span></p>

<p><b><strong>4. Minum air yang cukup</strong></b><br><span>Sering kali tubuh terasa lelah bukan karena kurang makan, tapi kurang cairan.</span></p>

<p><b><strong>5. Konsisten, bukan ekstrem</strong></b><br><span>Diet ketat yang menyiksa jarang bertahan lama. Pola makan sehat adalah kebiasaan jangka panjang.</span></p>

<p><br></p>

<p><b><strong>Tantangan di Era Modern</strong></b></p>

<p><span>Di era makanan cepat saji dan gaya hidup serba instan, menjaga pola makan menjadi tantangan besar. Jadwal sibuk membuat banyak orang memilih makanan praktis yang rendah nutrisi. Selain itu, pemasaran makanan tinggi gula dan lemak sering lebih menarik dibanding makanan sehat.</span></p>

<p><span>Kuncinya bukan menghindari semua makanan favorit, tetapi menciptakan keseimbangan. Pola makan sehat tetap memberi ruang untuk menikmati makanan kesukaan selama tidak menjadi kebiasaan harian.</span></p>

<p><br></p>

<p><span>Pola makan sehat bukan tujuan jangka pendek, melainkan gaya hidup. Perubahan kecil yang dilakukan secara konsisten jauh lebih efektif daripada perubahan drastis yang hanya bertahan sebentar. Mulai dari memilih air putih daripada minuman manis, menambah sayur di setiap piring, atau mengurangi makanan olahan, langkah sederhana ini dapat memberi dampak besar dalam jangka panjang.</span></p>

<p><span>Tubuh yang sehat dimulai dari pilihan makan yang kita buat setiap hari.</span></p>
HTML,
                'admin_id'     => 11,
                'created_at'   => '2026-02-13 17:11:06',
                'updated_at'   => '2026-02-13 17:11:06',
            ],
            [
                'guide_id'     => 12,
                'slug'         => 'keseimbangan-gizi-fondasi-tubuh-sehat-dan-produktif-vmfiLn',
                'published_at' => '2026-02-13 17:17:41',
                'title'        => 'Keseimbangan Gizi: Fondasi Tubuh Sehat dan Produktif',
                'content'      => <<<'HTML'
<p><span>Keseimbangan gizi adalah kondisi ketika tubuh mendapatkan semua zat gizi yang dibutuhkan dalam jumlah yang cukup dan proporsional. Tubuh manusia memerlukan karbohidrat, protein, lemak, vitamin, mineral, dan air untuk bekerja secara optimal. Kekurangan atau kelebihan salah satu unsur gizi dapat mengganggu fungsi tubuh dan memicu berbagai masalah kesehatan.</span></p>

<p><br></p>

<p><span>Konsep keseimbangan gizi menekankan bahwa tidak ada makanan yang sepenuhnya “baik” atau “buruk”. Yang menentukan adalah komposisi, variasi, dan kebiasaan konsumsi secara keseluruhan. Menurut panduan dari World Health Organization, pola makan seimbang harus memenuhi kebutuhan energi sekaligus menjaga keberagaman nutrisi agar tubuh tetap sehat dalam jangka panjang.</span></p>

<p><br></p>

<h3><span>Komponen Utama Keseimbangan Gizi</span></h3>

<p><b><strong>1. Karbohidrat sebagai sumber energi</strong></b><br><span>Karbohidrat adalah bahan bakar utama tubuh. Pilih karbohidrat kompleks seperti nasi merah, gandum utuh, atau umbi-umbian karena memberikan energi yang lebih stabil dibanding gula sederhana.</span></p>

<p><b><strong>2. Protein untuk perbaikan sel</strong></b><br><span>Protein berperan dalam pertumbuhan dan perbaikan jaringan tubuh. Sumber protein dapat berasal dari hewani (telur, ikan, ayam) maupun nabati (tahu, tempe, kacang-kacangan).</span></p>

<p><b><strong>3. Lemak sehat</strong></b><br><span>Tidak semua lemak berbahaya. Lemak baik dari alpukat, kacang, dan ikan membantu fungsi otak serta penyerapan vitamin.</span></p>

<p><b><strong>4. Vitamin dan mineral</strong></b><br><span>Zat gizi mikro ini menjaga sistem imun, metabolisme, dan kesehatan organ. Sayur dan buah berwarna-warni penting untuk memenuhi kebutuhan ini.</span></p>

<p><b><strong>5. Air</strong></b><br><span>Air sering dilupakan, padahal berperan dalam hampir semua proses tubuh, mulai dari pencernaan hingga pengaturan suhu.</span></p>

<p><br></p>

<h3><span>Dampak Ketidakseimbangan Gizi</span></h3>

<p><span>Keseimbangan gizi yang buruk dapat menimbulkan dua kondisi ekstrem: kekurangan gizi dan kelebihan gizi. Kekurangan gizi menyebabkan tubuh mudah lelah, daya tahan menurun, dan pertumbuhan terganggu. Sebaliknya, kelebihan asupan kalori tanpa keseimbangan nutrisi dapat memicu obesitas, diabetes, dan penyakit jantung.</span></p>

<p><span>Yang sering terjadi bukan kekurangan makan, tetapi kekurangan nutrisi berkualitas. Seseorang bisa makan banyak, namun tetap tidak mendapatkan zat gizi yang dibutuhkan tubuh.</span></p>

<p><br></p>

<h3><span>Cara Menerapkan Keseimbangan Gizi Sehari-hari</span></h3>

<ul><li><span>Isi setengah piring dengan sayur dan buah</span></li><li><span>Variasikan sumber protein setiap hari</span></li><li><span>Kurangi makanan ultra-proses</span></li><li><span>Batasi gula dan minuman manis</span></li><li><span>Perhatikan ukuran porsi</span></li><li><span>Biasakan makan teratur</span></li></ul>

<p><span>Langkah-langkah sederhana ini lebih efektif daripada diet ekstrem yang sulit dipertahankan.</span></p>

<p><br></p>

<p><span>Keseimbangan gizi bukan soal aturan kaku, melainkan kebiasaan cerdas dalam memilih makanan. Tubuh yang mendapatkan nutrisi seimbang akan bekerja lebih efisien, memberikan energi stabil, dan menurunkan risiko penyakit. Investasi kesehatan terbaik dimulai dari apa yang ada di piring kita setiap hari.</span></p>
HTML,
                'admin_id'     => 11,
                'created_at'   => '2026-02-13 17:17:41',
                'updated_at'   => '2026-02-13 17:17:41',
            ],
            [
                'guide_id'     => 13,
                'slug'         => 'makanan-sehat-saja-tidak-cukup-kesehatan-butuh-pola-hidup-seimbang-ZgOb1O',
                'published_at' => '2026-02-13 17:20:56',
                'title'        => 'Makanan Sehat Saja Tidak Cukup: Kesehatan Butuh Pola Hidup Seimbang',
                'content'      => <<<'HTML'
<p><span>Banyak orang fokus memperbaiki pola makan, tetapi melupakan bahwa kesehatan tidak hanya ditentukan oleh apa yang ada di piring. Tubuh manusia bekerja sebagai satu sistem yang dipengaruhi oleh tidur, aktivitas fisik, manajemen stres, dan kebiasaan harian. Makanan sehat tanpa pola hidup sehat ibarat bahan bakar bagus yang dimasukkan ke mesin yang jarang dirawat — hasilnya tidak optimal.</span></p>

<p><span>Menurut pedoman kesehatan global dari World Health Organization, kesehatan ideal adalah kombinasi nutrisi yang baik, aktivitas fisik cukup, istirahat berkualitas, dan keseimbangan mental. Artinya, diet sehat harus didampingi gaya hidup yang mendukung.</span></p>

<p><br></p>

<h3><span>Hubungan Makanan dan Pola Hidup</span></h3>

<p><span>Makanan memberi energi, tetapi bagaimana energi itu digunakan bergantung pada gaya hidup. Seseorang bisa makan makanan bergizi, tetapi jika kurang bergerak, kurang tidur, dan stres berkepanjangan, tubuh tetap mengalami gangguan.</span></p>

<p><span>Sebaliknya, olahraga rutin dan tidur cukup membantu tubuh menyerap nutrisi lebih efektif. Metabolisme menjadi stabil, hormon seimbang, dan sistem imun bekerja lebih kuat.</span></p>

<p><br></p>

<h3><span>Pilar Pola Hidup Sehat</span></h3>

<ol><li><b><strong>Aktivitas fisik rutin</strong></b><br><span>Olahraga membantu membakar energi, memperkuat jantung, dan menjaga massa otot. Tidak harus berat — berjalan kaki 30 menit per hari sudah memberi dampak besar.</span></li><li><b><strong>Tidur berkualitas</strong></b><br><span>Kurang tidur mengganggu hormon lapar, meningkatkan keinginan makan berlebih, dan menurunkan konsentrasi. Orang dewasa idealnya tidur 7–9 jam per malam.</span></li><li><b><strong>Manajemen stres</strong></b><br><span>Stres kronis memicu makan emosional dan gangguan metabolisme. Relaksasi, hobi, dan interaksi sosial penting untuk kesehatan mental.</span></li><li><b><strong>Konsistensi kebiasaan</strong></b><br><span>Kesehatan dibangun dari kebiasaan kecil yang dilakukan setiap hari, bukan perubahan ekstrem sesaat.</span></li></ol>

<p><br></p>

<h3><span>Kesalahan Umum yang Sering Terjadi</span></h3>

<p><span>Banyak orang menjalani diet ketat tetapi mengabaikan istirahat. Ada juga yang rajin olahraga namun tetap mengonsumsi makanan ultra-proses berlebihan. Ketidakseimbangan ini membuat hasil kesehatan tidak maksimal.</span></p>

<p><span>Kunci sebenarnya bukan kesempurnaan, tetapi harmoni antara makan, bergerak, dan beristirahat.</span></p>

<p><br></p>

<p><span>Kesehatan bukan proyek jangka pendek, melainkan gaya hidup jangka panjang. Makanan sehat adalah fondasi, tetapi pola hidup sehat adalah struktur yang menopangnya. Ketika keduanya berjalan bersama, tubuh menjadi lebih kuat, energi stabil, dan kualitas hidup meningkat.</span></p>

<p><span>Tubuh yang sehat bukan hanya hasil dari pilihan makan yang baik — tetapi juga dari cara kita menjalani hari.</span></p>
HTML,
                'admin_id'     => 11,
                'created_at'   => '2026-02-13 17:20:56',
                'updated_at'   => '2026-02-17 04:56:30',
            ],
            [
                'guide_id'     => 14,
                'slug'         => 'membangun-massa-otot-fondasi-nutrisi-kekuatan-dan-pertumbuhan-karakter-6pr2Xd',
                'published_at' => '2026-02-17 04:54:32',
                'title'        => 'Membangun Massa Otot: Fondasi Nutrisi, Kekuatan, dan Pertumbuhan Karakter',
                'content'      => <<<'HTML'
<p><span>Membangun otot bukan sekadar soal mengangkat beban berat di gym. Apa yang kita konsumsi setelah latihan adalah penentu utama apakah jaringan otot akan pulih dan berkembang atau justru menyusut. Banyak orang terjebak pada latihan intens namun mengabaikan asupan nutrisi, padahal otot tumbuh saat kita beristirahat dengan bahan bakar yang tepat.</span></p>

<p><span>Menurut prinsip sport science, pertumbuhan otot (hipertrofi) memerlukan kombinasi antara stimulasi latihan beban, asupan protein yang cukup, dan surplus energi yang terkontrol. Artinya, kualitas nutrisi sama pentingnya dengan intensitas latihan.</span></p>

<p><br></p>

<h3><b><strong>Mengapa Nutrisi untuk Otot Itu Penting?</strong></b></h3>

<p><span>Otot manusia bekerja melalui proses mikrotrauma saat latihan. Nutrisi berperan sebagai "semen dan batu bata" untuk menambal robekan kecil tersebut agar otot kembali lebih kuat dan besar. Tanpa nutrisi yang memadai, tubuh akan masuk ke fase katabolik (pemecahan otot) untuk mencari energi.</span></p>

<p><span>Nutrisi yang tepat untuk massa otot membantu:</span></p>

<ul><li><b><strong>Mempercepat pemulihan</strong></b><span> pasca latihan.</span></li><li><b><strong>Meningkatkan sintesis protein</strong></b><span> otot.</span></li><li><b><strong>Menyediakan energi</strong></b><span> untuk performa latihan maksimal.</span></li><li><b><strong>Menjaga keseimbangan hormon</strong></b><span> (seperti testosteron dan hormon pertumbuhan).</span></li><li><b><strong>Mencegah cedera</strong></b><span> akibat kelelahan jaringan.</span></li></ul>

<p><br></p>

<h3><b><strong>Prinsip Dasar Diet Hipertrofi</strong></b></h3>

<p><span>Membangun otot tidak harus berarti makan berlebihan tanpa aturan (</span><i><em>dirty bulking</em></i><span>). Kuncinya adalah strategi makronutrisi yang cerdas:</span></p>

<ol><li><b><strong>Prioritaskan Protein Berkualitas Protein adalah zat pembangun utama. </strong></b><span>Targetkan asupan sekitar 1.6g hingga 2.2g protein per kg berat badan. Sumber seperti dada ayam, telur, tempe, ikan, dan daging sapi rendah lemak harus ada dalam setiap porsi makan.</span></li><li><b><strong>Karbohidrat sebagai Bahan Bakar Jangan menjauhi karbohidrat. </strong></b><span>Karbohidrat mengisi simpanan glikogen otot yang menjadi sumber tenaga utama saat angkat beban. Pilihlah karbohidrat kompleks seperti nasi merah, ubi, atau oat untuk energi yang stabil.</span></li><li><b><strong>Surplus Kalori yang Terukur Untuk membangun jaringan baru, tubuh butuh energi ekstra. </strong></b><span>Namun, surplus yang terlalu besar hanya akan menambah lemak. Tambahkan sekitar 250–500 kalori di atas kebutuhan harian Anda (TDEE).</span></li><li><b><strong>Lemak Sehat untuk Hormon Lemak dari alpukat, kacang-kacangan, dan minyak zaitun</strong></b><span> sangat penting untuk menjaga fungsi hormon yang mendukung pertumbuhan otot.</span></li><li><b><strong>Hidrasi dan Jendela Nutrisi Otot terdiri dari sekitar 75% air. Dehidrasi sedikit saja dapat menurunkan kekuatan secara drastis.</strong></b><span> Selain itu, pastikan mengonsumsi protein dan karbohidrat dalam rentang waktu 1–3 jam setelah latihan untuk memaksimalkan pemulihan.</span></li></ol>

<p><br></p>

<h3><b><strong>Tantangan di Era Gaya Hidup Instan</strong></b></h3>

<p><span>Di tengah kesibukan, memenuhi kebutuhan protein harian sering kali sulit. Banyak orang tergoda menggunakan suplemen secara berlebihan tanpa memperbaiki pola makan utama. Suplemen seperti </span><i><em>whey protein</em></i><span> atau </span><i><em>creatine</em></i><span> hanyalah "pelengkap", bukan pengganti makanan utuh.</span></p>

<p><br></p>

<p><span>Kuncinya adalah </span><b><strong>progresi dan konsistensi</strong></b><span>. Membangun massa otot adalah maraton, bukan sprint. Hasil nyata biasanya baru terlihat setelah 8–12 minggu disiplin dalam pola makan dan latihan yang terprogram.</span></p>

<p><span>Membangun massa otot adalah investasi jangka panjang untuk kesehatan metabolik dan mobilitas di masa tua. Perubahan kecil seperti memastikan ada sumber protein di setiap piring atau menyiapkan bekal makanan sehat dapat membuat perbedaan besar pada transformasi fisik Anda.</span></p>

<p><span>Tubuh yang kuat dibangun di gym, namun dibentuk di dapur.</span></p>

<p><br></p>
HTML,
                'admin_id'     => 11,
                'created_at'   => '2026-02-17 04:54:32',
                'updated_at'   => '2026-02-17 04:59:48',
            ],
            [
                'guide_id'     => 15,
                'slug'         => 'the-art-of-healthy-living-vCJ8Fu',
                'published_at' => '2026-02-17 05:02:06',
                'title'        => 'The Art of Healthy Living',
                'content'      => <<<'HTML'
<p><span>Menemukan Titik Tengah dalam Pola Hidup Modern, Hidup di era modern adalah sebuah paradoks. Di satu sisi, teknologi memudahkan segalanya; di sisi lain, tuntutan kecepatan dan kenyamanan instan sering kali mengorbankan aset paling berharga yang kita miliki: kesehatan. Menjalani hidup sehat saat ini bukan lagi sekadar mengikuti tren diet terbaru, melainkan sebuah seni untuk menemukan titik tengah antara disiplin dan fleksibilitas.</span></p>

<p><br></p>

<p><span>Sehat yang sejati tidak ditemukan dalam ekstremitas—seperti diet yang menyiksa atau olahraga berlebihan yang memicu stres. Sehat adalah tentang harmoni antara tubuh, pikiran, dan lingkungan di tengah kebisingan dunia digital.</span></p>

<p><br></p>

<h3><b><strong>1. Nutrisi: Kualitas di Atas Restriksi</strong></b></h3>

<p><span>Banyak orang terjebak dalam pola pikir "apa yang tidak boleh dimakan". Padahal, seni hidup sehat modern lebih menekankan pada "apa yang harus dipenuhi". Tubuh kita membutuhkan nutrisi fungsional untuk berpikir jernih dan bergerak aktif.</span></p>

<ul><li><b><strong>Prinsip 80/20:</strong></b><span> Konsumsi 80% makanan utuh (</span><i><em>whole foods</em></i><span>) yang kaya nutrisi, dan berikan ruang 20% untuk menikmati makanan favorit agar mental tidak merasa tertekan.</span></li><li><b><strong>Sadar Nutrisi:</strong></b><span> Belajarlah membaca label kemasan. Kurangi ketergantungan pada makanan ultra-proses yang tinggi natrium dan pemanis buatan yang sering tersembunyi di balik label "praktis".</span></li></ul>

<h3><b><strong>2. Gerak: Mobilitas Sebagai Perayaan, Bukan Hukuman</strong></b></h3>

<p><span>Dalam budaya menetap (</span><i><em>sedentary</em></i><span>) saat ini, bergerak sering dianggap sebagai beban. Padahal, tubuh manusia dirancang untuk bergerak.</span></p>

<ul><li><b><strong>Gerak Fungsional:</strong></b><span> Jangan hanya mengandalkan jam gym. Naik tangga, berjalan kaki saat menelepon, atau melakukan peregangan di sela kerja adalah bagian dari seni bergerak.</span></li><li><b><strong>Temukan Kesenangan:</strong></b><span> Pilih jenis aktivitas yang Anda nikmati. Jika Anda membenci lari, cobalah berenang atau bersepeda. Konsistensi lahir dari kesenangan, bukan paksaan.</span></li></ul>

<h3><b><strong>3. Pemulihan: Kekuatan dalam Keheningan</strong></b></h3>

<p><span>Era modern memuja kesibukan dan menganggap istirahat sebagai kemalasan. Namun, pertumbuhan dan perbaikan sel tubuh terjadi saat kita berhenti sejenak.</span></p>

<ul><li><b><strong>Kualitas Tidur:</strong></b><span> Tidur 7–8 jam adalah kebutuhan biologis yang tidak bisa ditawar. Matikan layar perangkat satu jam sebelum tidur untuk menjaga ritme sirkadian tubuh.</span></li><li><b><strong>Detoks Digital:</strong></b><span> Berikan waktu bagi otak untuk lepas dari banjir informasi. Ketenangan mental sangat berpengaruh pada regulasi hormon kortisol (hormon stres) yang berdampak pada kesehatan fisik.</span></li></ul>

<h3><b><strong>4. Mindfulness: Kesadaran dalam Setiap Pilihan</strong></b></h3>

<p><span>Seni hidup sehat sangat bergantung pada kesadaran (</span><i><em>mindfulness</em></i><span>). Mengapa kita makan? Apakah karena lapar atau karena stres? Mengapa kita begadang? Apakah karena pekerjaan atau sekadar </span><i><em>scrolling</em></i><span> tanpa tujuan? Dengan meningkatkan kesadaran, kita berhenti menjadi "autopilot" dalam gaya hidup instan dan mulai mengambil kendali atas pilihan-pilihan kecil setiap harinya.</span></p>

<p><br></p>

<h3><b><strong>Menemukan Titik Tengah</strong></b></h3>

<p><span>Menemukan titik tengah berarti memahami bahwa kesehatan adalah perjalanan panjang, bukan garis finis. Akan ada hari di mana Anda makan salad dan berolahraga, namun akan ada hari di mana Anda butuh istirahat total dan sepotong pizza. Itu tidak masalah.</span></p>

<p><span>Keseimbangan bukan berarti sempurna setiap saat, melainkan kemampuan untuk kembali ke jalur yang benar setiap kali kita goyah. Seni hidup sehat adalah memahami kapasitas diri sendiri dan menghargai tubuh sebagai satu-satunya tempat yang kita miliki untuk tinggal seumur hidup.</span></p>

<p><br></p>
HTML,
                'admin_id'     => 11,
                'created_at'   => '2026-02-17 05:02:06',
                'updated_at'   => '2026-02-17 05:02:06',
            ],
        ];

        foreach ($guides as $guide) {
            $slug = $guide['slug'];
            $sourcePath = public_path('assets/guides/' . $slug . '.jpg');

            $storedImagePath = null;

            if (File::exists($sourcePath)) {
                $storedImagePath = 'guides/' . $slug . '.jpg';

                Storage::disk('public')->put(
                    $storedImagePath,
                    File::get($sourcePath)
                );
            }

            DB::table('guides')->updateOrInsert(
                ['slug' => $slug],
                [
                    'guide_id'     => $guide['guide_id'],
                    'published_at' => $guide['published_at'],
                    'content'      => $guide['content'],
                    'title'        => $guide['title'],
                    'image'        => $storedImagePath,
                    'admin_id'     => $guide['admin_id'],
                    'created_at'   => $guide['created_at'],
                    'updated_at'   => $guide['updated_at'],
                ]
            );
        }
    }
}