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
        <div class="for_ajax_load"></div>
        <div class="clearfix"> </div>
        <!-- END HEADER & CONTENT DIVIDER -->
        <!-- BEGIN CONTAINER -->
            <div class="page-container">
                <!-- BEGIN SIDEBAR -->
                @include('layouts.menu')
                <div class="page-content-wrapper">
                    <!-- BEGIN CONTENT BODY -->
                    <div class="page-content">
                        <p class="page_header">Clickability</p>
                        <div class="clickabilitiSimpleAlert">
                            <p>
                                Clickability is the estimated likelihood that a user will click on any given video from a channel in the organic search results. A score is given the higher ranked a video is, down to lowest ranked video at #40 - having multiple videos ranking from a specific search result, will result in a higher likelihood of a click , and higher score. This is done per Keyword Group. Updated once per week
                            </p>
                            <span class="clickabilityAlertClose">X</span>
                        </div>
                        <div class="row">
                            <div class="dashboard-stat2 ">
                                @foreach($groupsCharts as $key=>$groupsChart)
                                    <div class="pie_chart" id="pie_chart_{{$key}}" data-chart="pie_chart_{{$key}}"></div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @include('layouts.footer')
        <div class="loading">
            <div class="circle2"></div>
            <div class="circle1"></div>
        </div>
    </body>

</html>