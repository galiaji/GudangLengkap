<?php

/*
|--------------------------------------------------------------------------
| Pesan Validasi (Bahasa Indonesia)
|--------------------------------------------------------------------------
| Dipakai otomatis oleh $request->validate() saat APP_LOCALE=id.
| Pesan khusus yang ditulis langsung di controller tetap diutamakan.
*/

return [

    'accepted'             => ':Attribute harus diterima.',
    'active_url'           => ':Attribute bukan URL yang valid.',
    'after'                => ':Attribute harus berisi tanggal setelah :date.',
    'after_or_equal'       => ':Attribute harus berisi tanggal setelah atau sama dengan :date.',
    'alpha'                => ':Attribute hanya boleh berisi huruf.',
    'alpha_dash'           => ':Attribute hanya boleh berisi huruf, angka, strip, dan garis bawah.',
    'alpha_num'            => ':Attribute hanya boleh berisi huruf dan angka.',
    'array'                => ':Attribute harus berupa daftar.',
    'before'               => ':Attribute harus berisi tanggal sebelum :date.',
    'before_or_equal'      => ':Attribute harus berisi tanggal sebelum atau sama dengan :date.',
    'between'              => [
        'array'   => ':Attribute harus memiliki :min sampai :max item.',
        'file'    => ':Attribute harus berukuran :min sampai :max kilobyte.',
        'numeric' => ':Attribute harus bernilai antara :min sampai :max.',
        'string'  => ':Attribute harus berisi :min sampai :max karakter.',
    ],
    'boolean'              => ':Attribute harus bernilai benar atau salah.',
    'confirmed'            => 'Konfirmasi :attribute tidak sesuai.',
    'date'                 => ':Attribute bukan tanggal yang valid.',
    'date_equals'          => ':Attribute harus berisi tanggal yang sama dengan :date.',
    'date_format'          => ':Attribute tidak sesuai format :format.',
    'decimal'              => ':Attribute harus memiliki :decimal angka desimal.',
    'different'            => ':Attribute dan :other harus berbeda.',
    'digits'               => ':Attribute harus terdiri dari :digits angka.',
    'digits_between'       => ':Attribute harus terdiri dari :min sampai :max angka.',
    'distinct'             => ':Attribute memiliki nilai yang sama (duplikat).',
    'email'                => ':Attribute harus berupa alamat email yang valid.',
    'ends_with'            => ':Attribute harus diakhiri salah satu dari: :values.',
    'exists'               => ':Attribute yang dipilih tidak ditemukan.',
    'file'                 => ':Attribute harus berupa file.',
    'filled'               => ':Attribute wajib diisi.',
    'gt'                   => [
        'array'   => ':Attribute harus memiliki lebih dari :value item.',
        'file'    => ':Attribute harus berukuran lebih dari :value kilobyte.',
        'numeric' => ':Attribute harus lebih besar dari :value.',
        'string'  => ':Attribute harus lebih dari :value karakter.',
    ],
    'gte'                  => [
        'array'   => ':Attribute harus memiliki minimal :value item.',
        'file'    => ':Attribute harus berukuran minimal :value kilobyte.',
        'numeric' => ':Attribute harus lebih besar dari atau sama dengan :value.',
        'string'  => ':Attribute minimal :value karakter.',
    ],
    'image'                => ':Attribute harus berupa gambar.',
    'in'                   => ':Attribute yang dipilih tidak valid.',
    'in_array'             => ':Attribute tidak ada di :other.',
    'integer'              => ':Attribute harus berupa bilangan bulat.',
    'ip'                   => ':Attribute harus berupa alamat IP yang valid.',
    'json'                 => ':Attribute harus berupa JSON yang valid.',
    'lt'                   => [
        'array'   => ':Attribute harus memiliki kurang dari :value item.',
        'file'    => ':Attribute harus berukuran kurang dari :value kilobyte.',
        'numeric' => ':Attribute harus lebih kecil dari :value.',
        'string'  => ':Attribute harus kurang dari :value karakter.',
    ],
    'lte'                  => [
        'array'   => ':Attribute maksimal memiliki :value item.',
        'file'    => ':Attribute maksimal berukuran :value kilobyte.',
        'numeric' => ':Attribute harus lebih kecil dari atau sama dengan :value.',
        'string'  => ':Attribute maksimal :value karakter.',
    ],
    'max'                  => [
        'array'   => ':Attribute maksimal memiliki :max item.',
        'file'    => ':Attribute maksimal berukuran :max kilobyte.',
        'numeric' => ':Attribute maksimal bernilai :max.',
        'string'  => ':Attribute maksimal :max karakter.',
    ],
    'mimes'                => ':Attribute harus berupa file bertipe: :values.',
    'mimetypes'            => ':Attribute harus berupa file bertipe: :values.',
    'min'                  => [
        'array'   => ':Attribute minimal memiliki :min item.',
        'file'    => ':Attribute minimal berukuran :min kilobyte.',
        'numeric' => ':Attribute minimal bernilai :min.',
        'string'  => ':Attribute minimal :min karakter.',
    ],
    'not_in'               => ':Attribute yang dipilih tidak valid.',
    'not_regex'            => 'Format :attribute tidak valid.',
    'numeric'              => ':Attribute harus berupa angka.',
    'password'             => [
        'letters'       => ':Attribute harus mengandung minimal satu huruf.',
        'mixed'         => ':Attribute harus mengandung huruf besar dan huruf kecil.',
        'numbers'       => ':Attribute harus mengandung minimal satu angka.',
        'symbols'       => ':Attribute harus mengandung minimal satu simbol.',
        'uncompromised' => ':Attribute ini pernah bocor di internet. Silakan pilih :attribute lain.',
    ],
    'present'              => ':Attribute wajib ada.',
    'regex'                => 'Format :attribute tidak valid.',
    'required'             => ':Attribute wajib diisi.',
    'required_if'          => ':Attribute wajib diisi bila :other adalah :value.',
    'required_unless'      => ':Attribute wajib diisi kecuali :other adalah :values.',
    'required_with'        => ':Attribute wajib diisi bila :values diisi.',
    'required_with_all'    => ':Attribute wajib diisi bila :values diisi.',
    'required_without'     => ':Attribute wajib diisi bila :values tidak diisi.',
    'required_without_all' => ':Attribute wajib diisi bila tidak ada :values yang diisi.',
    'same'                 => ':Attribute dan :other harus sama.',
    'size'                 => [
        'array'   => ':Attribute harus memiliki :size item.',
        'file'    => ':Attribute harus berukuran :size kilobyte.',
        'numeric' => ':Attribute harus bernilai :size.',
        'string'  => ':Attribute harus berisi :size karakter.',
    ],
    'starts_with'          => ':Attribute harus diawali salah satu dari: :values.',
    'string'               => ':Attribute harus berupa teks.',
    'timezone'             => ':Attribute harus berupa zona waktu yang valid.',
    'unique'               => ':Attribute sudah digunakan.',
    'uploaded'             => ':Attribute gagal diunggah.',
    'url'                  => 'Format :attribute tidak valid.',
    'uuid'                 => ':Attribute harus berupa UUID yang valid.',

    'custom' => [
        'password' => [
            'min' => 'Password minimal :min karakter.',
        ],
    ],

    /*
    | Nama field yang ditampilkan di pesan (pengganti nama teknis).
    */
    'attributes' => [
        // Umum
        'name'                  => 'nama',
        'username'              => 'username',
        'email'                 => 'email',
        'role'                  => 'role',
        'password'              => 'password',
        'password_confirmation' => 'konfirmasi password',

        // Transaksi kayu
        'atas_nama'          => 'atas nama',
        'wood_type_id'       => 'jenis kayu',
        'total_volume'       => 'total volume',
        'total_pembayaran'   => 'total pembayaran',
        'jenis_transaksi'    => 'jenis transaksi',
        'details'            => 'detail log kayu',
        'details.*.grade'    => 'grade',
        'details.*.diameter' => 'diameter',
        'details.*.panjang'  => 'panjang',
        'details.*.pcs'      => 'jumlah PCS',
        'details.*.volume'   => 'volume',
        'details.*.harga'    => 'harga',

        // Stok & harga kayu
        'grade'             => 'grade',
        'diameter'          => 'diameter',
        'panjang'           => 'panjang',
        'harga_beli_per_m3' => 'harga beli per m³',
        'harga_jual_per_m3' => 'harga jual per m³',

        // Palet
        'tanggal'               => 'tanggal',
        'tanggal_input'         => 'tanggal input',
        'customer'              => 'customer',
        'pallets'               => 'data palet',
        'pallets.*.pallet_code' => 'kode palet',
        'pallets.*.ukuran'      => 'ukuran palet',
        'pallets.*.pcs'         => 'jumlah PCS palet',
        'pallets.*.volume'      => 'volume palet',
        'harga_basah'           => 'harga basah',
        'harga_kering'          => 'harga kering',
    ],

];
