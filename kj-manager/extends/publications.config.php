<?php

define( 'PUBLICATION_FOLDER', '/pub' );
define( 'PUBLICATION_FOLDER_ABS', $_SERVER['DOCUMENT_ROOT'].'/pub' );

$publication_file_type_list = array(
    'slideshare' => array(
        'name' => 'SlideShare',
        'hint' => ''
    ),
    'fulltext' => array(
        'name' => '全文',
        'hint' => ''
    ),
    'fulltext_pdf' => array(
        'suffix' => '.pdf',
        'name' => '全文PDF檔',
        'icon'=> '_images/icon/pdf_s.jpg',
        'hint' => 'Full Text Available'
    ),
    'slides_ppt' => array(
        'suffix' => '_slides.ppt',
        'name' => '投影片(ppt)',
        'icon'=> '_images/icon/ppt.jpg',
        'hint' => 'Slides Available'
    ),
    'slides_pdf' => array(
        'suffix' => '_slides.pdf',
        'name' => '投影片(pdf)',
        'icon'=> '_images/icon/ppt.jpg',
        'hint' => 'Slides Available'
    ),
    'poster_pdf' => array(
        'suffix' => '_poster.pdf',
        'name' => '海報(pdf)',
        'icon'=> '_images/icon/pos.jpg',
        'hint' => 'Poster Available'
    )
);