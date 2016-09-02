<?php
/*-------------------------------------------------------------\
 * 檔案：_template/menu.php
 - 說明：所有頁面的目錄
\-------------------------------------------------------------*/
if( !isset($subtitle) OR !isset($lang) )
{
    header('location: /index.html');
    die();
}

$menulinks = array(
    'home' => array(
        'name' => array(
            'TC' => '首頁',
            'EN' => 'Home'
        ),
        'href' => '/index.html'
    ),
    'research' => array(
        'name' => array(
            'TC' => '研究領域',
            'EN' => 'Research'
        ),
        'href' => '/research.html'
    ),
    'members' => array(
        'name' => array(
            'TC' => '實驗室成員',
            'EN' => 'Members'
        ),
        'href' => '/members.html'
    ),
    'projects' => array(
        'name' => array(
            'TC' => '研究計劃',
            'EN' => 'Projects'
        ),
        'href' => '/project.html'
    ),
    'publications' => array(
        'name' => array(
            'TC' => '研究著作',
            'EN' => 'Publications'
        ),
        'href' => '/publication.html'
    ),
    // 'downloads' => array(
    //     'name' => array(
    //         'TC' => '下載',
    //         'EN' => 'Downloads'
    //     ),
    //     'href' => '/download.html'
    // ),
    // 'partners' => array(
    //     'name' => array(
    //         'TC' => '合作夥伴',
    //         'EN' => 'Partners'
    //     ),
    //     'href' => '/partners.html'
    // ),
    'resources' => array(
        'name' => array(
            'TC' => '實驗室資源',
            'EN' => 'Resources'
        ),
        'href' => '/resources.html',
        'user' => true
    )
);
?>
<div class="mainnav">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="main-navigation">
                    <!-- navbar start -->
                    <!-- ================ -->
                    <nav class="navbar navbar-default" role="navigation">
                        <div class="container">
                            <!-- Toggle get grouped for better mobile display -->
                            <div class="navbar-header">
                                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar-collapse-1">
                                    <span class="sr-only">Toggle navigation</span>
                                    <span class="icon-bar"></span>
                                    <span class="icon-bar"></span>
                                    <span class="icon-bar"></span>
                                </button>
                            </div>
                            <div class="collapse navbar-collapse dark" id="navbar-collapse-1">
                                <!-- main-menu -->
                                <ul class="nav navbar-nav">
                                    <!-- mega-menu start -->
                                    <?
                                        foreach ($menulinks as $menukey => $menulink)
                                        {
                                            $user = isset($menulink['user']) ? $menulink['user'] : false;
                                            if( $user AND !checkAdministrator() ) { continue; }
                                            $attrs = ' href="'.$menulink['href'].'"';
                                            $thispage_uri = $_SERVER['REQUEST_URI'];
                                            if( ($q = strpos($thispage_uri,'?')) > 0 ) { $thispage_uri = substr($thispage_uri,0,$q); }
                                            if( $thispage_uri == $menulink['href'] ) { $attrs = ' class="active"'; }
                                            
                                            echo '<li><a title="'.$menulink['name'][$lang].'" '.$attrs.'>'.$menulink['name'][$lang].'</a></li>';
                                        }
                                    ?>
                                </ul>
                                <!-- main-menu end -->
                            </div>
                        </div>
                    </nav>
                    <!-- navbar end -->
                </div>
            </div>
        </div>
    </div>
</div>
</header>
<!-- header end -->
</div>
<!-- header-container end -->