<?php
/*-------------------------------------------------------------\
 * 檔案：_template/header.php
 - 說明：所有頁面的開始及 banner 部分內容
 - 透過php內部參數 "lang" 設定語系
\-------------------------------------------------------------*/
require_once(rtrim($_SERVER['DOCUMENT_ROOT'],'/').'/kj-manager/mod_general.php');
if( !isset($subtitle) )
{
    header('location: /index.html');
    die();
}

if( !isset($lang) )
{
    if( isset($_GET['lang']) )
    {
        $_SESSION['lang'] = $_GET['lang'];
        $redirect = rtrim(preg_replace('/&?lang='.$_GET['lang'].'/','',$_SERVER['REQUEST_URI']),'?');
        header('location: '.$redirect);
        die();
    }
    $lang = 'TC';
    if( isset($_SESSION['lang']) ) { $lang = $_SESSION['lang']; }
    else { $_SESSION['lang'] = $lang; }
}

$toplinks = array(
    array(
        'href' => array(
            'TC' => 'http://www.sinica.edu.tw/',
            'EN' => 'http://www.sinica.edu.tw/main_e.shtml'
        ),
        'name' => array(
            'TC' => '中央研究院',
            'EN' => 'Academia Sinica'
        ),
        'target' => '_blank'
    ),
    array(
        'href' => array(
            'TC' => 'http://www.iis.sinica.edu.tw/',
            'EN' => 'http://www.iis.sinica.edu.tw/index_en.html',
        ),
        'name' => array(
            'TC' => '資訊科學研究所',
            'EN' => 'Institute of Information Science'
        ),
        'target' => '_blank'
    )
);

$languages = array(
    'TC' => array(
        'name' => '中文',
        'title' => '資料洞察實驗室',
        'login' => '登入',
        'logout' => '登出'
    ),
    'EN' => array(
        'name' => 'English',
        'title' => 'Data Insights Research Laboratory',
        'login' => 'Login',
        'logout' => 'Logout'
    ),
);
?>
    <!DOCTYPE html>
    <html>

    <head>
        <meta charset="utf-8" />
        <title><?=$languages[$lang]['title']?> | <?=$subtitle[$lang]?></title>
        <meta name="description" content="<?=$languages[$lang]['title']?>">
        <!-- Mobile Meta -->
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <!-- Favicon -->
        <link rel="shortcut icon" href="/favicon.ico">
        <!-- Bootstrap core CSS -->
        <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
        <!-- Font Awesome CSS -->
        <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-T8Gy5hrqNKT+hzMclPo118YTQO6cYprQmhrYwIiQ/3axmI1hQomh7Ud2hPOy8SP1" crossorigin="anonymous">
        <!-- Plugins -->
        <link href="/_styles/animations.css" rel="stylesheet">
        <!-- The core CSS file -->
        <link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Ubuntu" />
        <link href="/_styles/style.css" rel="stylesheet">

        <script type="text/javascript" src="/_scripts/mmnet.js"></script>
    </head>

    <body class="no-trans front-page transparent-header">
        <!-- scrollToTop -->
        <!-- ================ -->
        <div class="scrollToTop circle"><i class="fa fa-chevron-up"></i></div>
        <!-- page wrapper start -->
        <!-- ================ -->
        <div class="page-wrapper">
            <!-- header-container start -->
            <div class="header-container">
                <header class="header clearfix">
                    <div class="container">
                        <div class="row">
                            <div class="col-md-5 ">
                                <!-- header-left start -->
                                <!-- ================ -->
                                <div class="header-left clearfix">
                                    <!-- logo -->
                                    <span class="logo"><a href="/index.html" title="<?=$languages[$lang]['title']?>"><img id="logo_img" src="/_images/logo/dlogo-<?=$lang?>.svg" alt="<?=$languages[$lang]['title']?>"></a></span>
                                    
                                </div>
                                <!-- header-left end -->
                            </div>
                            <div class="col-md-7">
                                <!-- header-right start -->
                                <!-- ================ -->
                                <div class="header-right clearfix">
                                    <div id="header-top-second" class="clearfix">
                                        <div class="header-top-dropdown text-right">
                                            <?
                                                foreach ($languages as $key => $language)
                                                {
                                                    if( $key == $lang ) { continue; }
                                                    $querys = array();
                                                    foreach( explode('&',$_SERVER['QUERY_STRING']) as $query )
                                                    {
                                                        $match = array();
                                                        if( preg_match('/(.*)=(.*)/',$query,$match) != 1 ) { continue; }
                                                        if( $match[1] != 'lang' ) { $querys[] = $query; }
                                                    }
                                                    $querys[] = 'lang=' . $key;
                                            
                                                    echo '<div class="btn-group"><a href="?'.implode('&',$querys).'" class="btn btn-top btn-sm"><i class="fa fa-globe pr-10"></i>'.$language['name'].'</a></div>';
                                                }

                                                $log_href = '/login.html';
                                                $log_suffix = 'in';
                                                if( checkAdministrator() )
                                                {
                                                    $log_href = '/kj-manager/act/logout.php?redirect=/';
                                                    $log_suffix = 'out';
                                                }
                                            ?>

                                            <div class="btn-group">
                                               <a href="<?=$log_href?>" title="<?=$languages[$lang]['log'.$log_suffix]?>" class="btn btn-top btn-sm"><i class="fa fa-user pr-10"></i> <?=$languages[$lang]['log'.$log_suffix]?></a>
                                            </div>
                                        </div>
                                        <!--  header top dropdowns end -->
                                    </div>
                                    <div class="hearder-sublink">
                                       <?
                                            $topmap = array();
                                            $toplinks = array_reverse($toplinks);
                                            foreach ($toplinks as $toplink)
                                            {
                                                $name = $toplink['name'][$lang];
                                                $topmap[] = '<a href="'.$toplink['href'][$lang].'" title="'.$name.'" target="'.$toplink['target'].'">'.$name.'</a>';
                                            }
                                            echo implode('&nbsp;&nbsp;|&nbsp;&nbsp;',$topmap);
                                        ?>
                                    </div>
                                    <!-- main-navigation end -->
                                </div>
                                <!-- header-right end -->
                            </div>
                        </div>
                    </div>
