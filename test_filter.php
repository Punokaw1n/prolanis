<?php

$dataPasien = ['no_rm' => '123', 'pendidikan' => 'SLTA', 'pekerjaan' => 'PNS'];
$filtered = array_filter($dataPasien, function ($val) {
    return $val !== '' && $val !== null && $val !== ', , , ';
});
print_r($filtered);
