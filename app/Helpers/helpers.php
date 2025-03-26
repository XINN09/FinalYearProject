<?php

if (!function_exists('getFileIcon')) {
    function getFileIcon($fileType)
    {
        $icons = [
            'pdf'  => asset('images/pdf-icon.png'),
            'doc'  => asset('images/doc-icon.png'),
            'docx' => asset('images/docx-icon.png'),
            'xls'  => asset('images/xls-icon.png'),
            'xlsx' => asset('images/xlsx-icon.png'),
            'jpg'  => asset('images/jpg-icon.png'),
            'jpeg' => asset('images/jpeg-icon.png'),
            'png'  => asset('images/png-icon.png'),
            'txt'  => asset('images/txt-icon.png'),
        ];
        return $icons[$fileType] ?? asset('images/default-icon.png');
    }
}

