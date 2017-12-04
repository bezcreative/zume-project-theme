<?php
/*
Template Name: Zúme Stats
*/
if ( !current_user_can( "administrator" ) ){
    wp_redirect( "dashboard" );
}

get_header();


?>

    <div id="content">
        <div id="inner-content" class="row">

            <main id="main" class="large-12 medium-12 columns" role="main">

                <div id="sizes" style="width: 900px; height: 600px;"></div>
                <div id="sessions" style="width: 900px; height: 600px;"></div>

                <h3>Groups locations</h3>
                <div id="group-markers" style="width: 900px; height: 600px;"></div>

                <br>
                <br>
                <h3>Other stats</h3>
                <div>Homepage page views: <span id="analytics"></span></div>
                <div>Intro video views: <span id="intro_views"></span></div>
            </main>
        </div>
    </div>


