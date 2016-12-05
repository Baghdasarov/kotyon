<!DOCTYPE html>
<html lang="en">
    <head>
        @include('layouts.header')        
    </head>
    <!-- END HEAD -->

    <body class="page-header-fixed page-sidebar-closed-hide-logo page-container-bg-solid">
        @include('layouts.pageheader')
        <!-- END HEADER -->
        <!-- BEGIN HEADER & CONTENT DIVIDER -->
        <div class="clearfix"> </div>
        <!-- END HEADER & CONTENT DIVIDER -->
        <!-- BEGIN CONTAINER -->
        <div class="page-container">
            <!-- BEGIN SIDEBAR -->

            @include('layouts.menu')
            <div class="page-content-wrapper">
                <!-- BEGIN CONTENT BODY -->
                <div class="page-content">
                    <!-- BEGIN PAGE HEADER-->                    
                    <h1 class="page-title">Dashboard
                        <small>statistics, charts, recent events and reports</small>
                    </h1>
                    <div class="page-bar">
                        <ul class="page-breadcrumb">
                            <li>
                                <i class="icon-home"></i>
                                <a href="index.html">Home</a>
                                <i class="fa fa-angle-right"></i>
                            </li>
                            <li>
                                <span>Dashboard</span>
                            </li>
                        </ul>
                        <div class="page-toolbar"></div>
                    </div>
                    <!-- END PAGE HEADER-->
                    <h1>DASHBOARD</h1>
                    
                </div>
                <!-- END CONTENT BODY -->
            </div>
        </div>
        <!-- END CONTAINER -->
        <!-- BEGIN FOOTER -->
        <div class="page-footer">
            {{--<div class="page-footer-inner"> 2016 &copy; Metronic Theme By--}}
                {{--<a target="_blank" href="http://keenthemes.com">Keenthemes</a> &nbsp;|&nbsp;--}}
                {{--<a href="http://themeforest.net/item/metronic-responsive-admin-dashboard-template/4021469?ref=keenthemes" title="Purchase Metronic just for 27$ and get lifetime updates for free" target="_blank">Purchase Metronic!</a>--}}
                {{--<div class="scroll-to-top">--}}
                    {{--<i class="icon-arrow-up"></i>--}}
                {{--</div>--}}
            {{--</div>--}}
            <!-- END FOOTER -->
            <!-- BEGIN QUICK NAV -->
            <nav class="quick-nav" style="display:none;">
                <a class="quick-nav-trigger" href="#0">
                    <span aria-hidden="true"></span>
                </a>
                <ul>
                    <li>
                        <a href="https://themeforest.net/item/metronic-responsive-admin-dashboard-template/4021469?ref=keenthemes" target="_blank" class="active">
                            <span>Purchase Metronic</span>
                            <i class="icon-basket"></i>
                        </a>
                    </li>
                    <li>
                        <a href="https://themeforest.net/item/metronic-responsive-admin-dashboard-template/reviews/4021469?ref=keenthemes" target="_blank">
                            <span>Customer Reviews</span>
                            <i class="icon-users"></i>
                        </a>
                    </li>
                    <li>
                        <a href="http://keenthemes.com/showcast/" target="_blank">
                            <span>Showcase</span>
                            <i class="icon-user"></i>
                        </a>
                    </li>
                    <li>
                        <a href="http://keenthemes.com/metronic-theme/changelog/" target="_blank">
                            <span>Changelog</span>
                            <i class="icon-graph"></i>
                        </a>
                    </li>
                </ul>
                <span aria-hidden="true" class="quick-nav-bg"></span>
            </nav>
            <div class="quick-nav-overlay"></div>
            @include('layouts.footer')
    </body>

</html>