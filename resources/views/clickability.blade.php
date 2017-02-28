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

                                <div class="row">
                                    <div class="col-md-12">
                                        <!-- Modal Start here-->

                                        <div class="modal fade" id="showGraphModalClickability" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                        <h4 class="modal-title" id="showGraphModalModalLabel">Show Graph</h4>
                                                    </div>
                                                    <div class="modal-body" id="showGraphModalBodyClickability">

                                                    </div>
                                                    <div class="modal-footer">

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Modal ends Here -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    @include('layouts.footer')
        <div class="loading">
            <div class="spinner">
                <div class="bar1"></div>
                <div class="bar2"></div>
                <div class="bar3"></div>
                <div class="bar4"></div>
                <div class="bar5"></div>
                <div class="bar6"></div>
                <div class="bar7"></div>
                <div class="bar8"></div>
                <div class="bar9"></div>
                <div class="bar10"></div>
                <div class="bar11"></div>
                <div class="bar12"></div>
            </div>
        </div>
    </body>

</html>